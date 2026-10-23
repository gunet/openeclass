import fs from 'node:fs';
import path from 'node:path';
import zlib from 'node:zlib';

/**
 * Upload fixtures, generated on first use into `sample-data/.generated/` (gitignored), so the repo holds no
 * binary blobs and every file is small and predictable:
 *
 *   await page.setInputFiles('input[type=file]', testFile('pdf'));
 *
 * Not here: a course backup zip (its format is the app's own; the backup spec should create one through
 * the UI and restore that), and a real H5P package (`h5p` below has the package layout but no libraries,
 * enough for upload validation, not for playback).
 */

const DIR = path.join(__dirname, '..', 'sample-data', '.generated');

// --- tiny zip writer (stored entries, no compression) ----------------------------------------------------

const CRC_TABLE = Array.from({ length: 256 }, (_, n) => {
  let c = n;
  for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
  return c >>> 0;
});

function crc32(data: Buffer): number {
  let crc = 0xffffffff;
  for (const byte of data) crc = CRC_TABLE[(crc ^ byte) & 0xff] ^ (crc >>> 8);
  return (crc ^ 0xffffffff) >>> 0;
}

/** A zip archive with the given files (path → content), stored uncompressed. */
export function zip(files: Record<string, string | Buffer>): Buffer {
  const locals: Buffer[] = [];
  const centrals: Buffer[] = [];
  let offset = 0;
  for (const [name, content] of Object.entries(files)) {
    const data = Buffer.isBuffer(content) ? content : Buffer.from(content, 'utf8');
    const nameBytes = Buffer.from(name, 'utf8');
    const crc = crc32(data);
    const local = Buffer.alloc(30);
    local.writeUInt32LE(0x04034b50, 0);
    local.writeUInt16LE(20, 4);          // version needed
    local.writeUInt16LE(0x0800, 6);      // UTF-8 names
    local.writeUInt16LE(0, 8);           // stored
    local.writeUInt32LE(0x00210000, 10); // 1980-01-01 00:00
    local.writeUInt32LE(crc, 14);
    local.writeUInt32LE(data.length, 18);
    local.writeUInt32LE(data.length, 22);
    local.writeUInt16LE(nameBytes.length, 26);
    locals.push(local, nameBytes, data);

    const central = Buffer.alloc(46);
    central.writeUInt32LE(0x02014b50, 0);
    central.writeUInt16LE(20, 4);
    central.writeUInt16LE(20, 6);
    central.writeUInt16LE(0x0800, 8);
    central.writeUInt16LE(0, 10);
    central.writeUInt32LE(0x00210000, 12);
    central.writeUInt32LE(crc, 16);
    central.writeUInt32LE(data.length, 20);
    central.writeUInt32LE(data.length, 24);
    central.writeUInt16LE(nameBytes.length, 28);
    central.writeUInt32LE(offset, 42);
    centrals.push(central, nameBytes);
    offset += local.length + nameBytes.length + data.length;
  }
  const centralSize = centrals.reduce((size, part) => size + part.length, 0);
  const end = Buffer.alloc(22);
  end.writeUInt32LE(0x06054b50, 0);
  end.writeUInt16LE(Object.keys(files).length, 8);
  end.writeUInt16LE(Object.keys(files).length, 10);
  end.writeUInt32LE(centralSize, 12);
  end.writeUInt32LE(offset, 16);
  return Buffer.concat([...locals, ...centrals, end]);
}

// --- generators -----------------------------------------------------------------------------------------

/** A one-page PDF with the given text, with a correct xref table. */
function pdf(text: string): Buffer {
  const stream = `BT /F1 24 Tf 72 720 Td (${text.replace(/[()\\]/g, '\\$&')}) Tj ET`;
  const objects = [
    '<< /Type /Catalog /Pages 2 0 R >>',
    '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
    '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
    `<< /Length ${stream.length} >>\nstream\n${stream}\nendstream`,
    '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
  ];
  let out = '%PDF-1.4\n';
  const offsets: number[] = [];
  objects.forEach((body, i) => {
    offsets.push(out.length);
    out += `${i + 1} 0 obj\n${body}\nendobj\n`;
  });
  const xref = out.length;
  out += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n`;
  out += offsets.map((o) => `${String(o).padStart(10, '0')} 00000 n \n`).join('');
  out += `trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF\n`;
  return Buffer.from(out, 'latin1');
}

/** A width×height PNG filled with one colour. */
function png(width = 2, height = 2, rgb: [number, number, number] = [0, 102, 204]): Buffer {
  const chunk = (type: string, data: Buffer) => {
    const length = Buffer.alloc(4);
    length.writeUInt32BE(data.length);
    const body = Buffer.concat([Buffer.from(type, 'ascii'), data]);
    const crc = Buffer.alloc(4);
    crc.writeUInt32BE(crc32(body));
    return Buffer.concat([length, body, crc]);
  };
  const header = Buffer.alloc(13);
  header.writeUInt32BE(width, 0);
  header.writeUInt32BE(height, 4);
  header.writeUInt8(8, 8); // bit depth
  header.writeUInt8(2, 9); // truecolour
  const row = Buffer.concat([Buffer.from([0]), ...Array.from({ length: width }, () => Buffer.from(rgb))]);
  const pixels = zlib.deflateSync(Buffer.concat(Array.from({ length: height }, () => row)));
  return Buffer.concat([
    Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]),
    chunk('IHDR', header),
    chunk('IDAT', pixels),
    chunk('IEND', Buffer.alloc(0)),
  ]);
}

function docx(text: string): Buffer {
  return zip({
    '[Content_Types].xml': '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      + '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
      + '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
      + '<Default Extension="xml" ContentType="application/xml"/>'
      + '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
      + '</Types>',
    '_rels/.rels': '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      + '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
      + '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
      + '</Relationships>',
    'word/document.xml': '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      + '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
      + `<w:body><w:p><w:r><w:t>${text}</w:t></w:r></w:p></w:body></w:document>`,
  });
}

/** A SCORM 1.2 package with one SCO that reports completion. */
function scorm(): Buffer {
  return zip({
    'imsmanifest.xml': `<?xml version="1.0" encoding="UTF-8"?>
<manifest identifier="E2E_SCORM" version="1.0"
  xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2"
  xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2">
  <metadata><schema>ADL SCORM</schema><schemaversion>1.2</schemaversion></metadata>
  <organizations default="ORG1">
    <organization identifier="ORG1">
      <title>E2E SCORM package</title>
      <item identifier="ITEM1" identifierref="RES1"><title>E2E lesson</title></item>
    </organization>
  </organizations>
  <resources>
    <resource identifier="RES1" type="webcontent" adlcp:scormtype="sco" href="index.html">
      <file href="index.html"/>
    </resource>
  </resources>
</manifest>
`,
    'index.html': `<!doctype html>
<html><head><meta charset="utf-8"><title>E2E lesson</title><script>
function api(w) { while (w) { if (w.API) return w.API; if (w.parent === w) break; w = w.parent; } return window.opener && window.opener.API; }
window.onload = function () { var a = api(window); if (a) { a.LMSInitialize(''); a.LMSSetValue('cmi.core.lesson_status', 'completed'); a.LMSCommit(''); } };
window.onunload = function () { var a = api(window); if (a) a.LMSFinish(''); };
</script></head><body><h1>E2E SCORM lesson</h1></body></html>
`,
  });
}

/** IMS QTI 1.2 with one single-answer multiple choice question. */
const QTI = `<?xml version="1.0" encoding="UTF-8"?>
<questestinterop>
  <item ident="E2E_Q1" title="E2E capital question">
    <presentation>
      <material><mattext texttype="text/plain">What is the capital of Greece?</mattext></material>
      <response_lid ident="RESPONSE" rcardinality="Single">
        <render_choice>
          <response_label ident="A"><material><mattext>Athens</mattext></material></response_label>
          <response_label ident="B"><material><mattext>Sparta</mattext></material></response_label>
        </render_choice>
      </response_lid>
    </presentation>
    <resprocessing>
      <outcomes><decvar varname="SCORE" vartype="Decimal" defaultval="0"/></outcomes>
      <respcondition continue="No">
        <conditionvar><varequal respident="RESPONSE">A</varequal></conditionvar>
        <setvar varname="SCORE" action="Set">1</setvar>
      </respcondition>
    </resprocessing>
  </item>
</questestinterop>
`;

const GIFT = `// E2E GIFT questions
::E2E capital:: What is the capital of Greece? {=Athens ~Sparta ~Thessaloniki}

::E2E true-false:: The Parthenon is in Athens. {T}

::E2E short answer:: Two plus two equals {=4 =four}.
`;

const AIKEN = `What is the capital of Greece?
A. Athens
B. Sparta
C. Thessaloniki
ANSWER: A
`;

/** Bulk registration (modules/admin/multireguser.php): field names first; extra columns are course codes. */
const USERS_CSV = `first,last,email,id,phone,username,password
Bulk,One,e2e_bulk1@example.com,AM001,,e2e_bulk1,E2e-Bulk-1!,E2EOPEN
Bulk,Two,e2e_bulk2@example.com,AM002,,e2e_bulk2,E2e-Bulk-2!
`;

/** An .h5p with the package layout (h5p.json + content) but no bundled libraries. */
function h5p(): Buffer {
  return zip({
    'h5p.json': JSON.stringify({
      title: 'E2E H5P',
      language: 'en',
      mainLibrary: 'H5P.Text',
      embedTypes: ['div'],
      preloadedDependencies: [{ machineName: 'H5P.Text', majorVersion: 1, minorVersion: 1 }],
    }),
    'content/content.json': JSON.stringify({ text: '<p>E2E H5P content</p>' }),
  });
}

const GENERATORS = {
  pdf: { name: 'e2e-document.pdf', make: () => pdf('E2E test document') },
  png: { name: 'e2e-image.png', make: () => png() },
  docx: { name: 'e2e-document.docx', make: () => docx('E2E test document') },
  txt: { name: 'e2e-notes.txt', make: () => Buffer.from('E2E plain text file\n') },
  zip: { name: 'e2e-archive.zip', make: () => zip({ 'readme.txt': 'E2E archive\n', 'folder/inner.txt': 'inside a folder\n' }) },
  scorm: { name: 'e2e-scorm12.zip', make: scorm },
  qti: { name: 'e2e-qti.xml', make: () => Buffer.from(QTI) },
  gift: { name: 'e2e-questions.gift.txt', make: () => Buffer.from(GIFT) },
  aiken: { name: 'e2e-questions.aiken.txt', make: () => Buffer.from(AIKEN) },
  usersCsv: { name: 'e2e-users.csv', make: () => Buffer.from(USERS_CSV) },
  h5p: { name: 'e2e-content.h5p', make: h5p },
  /** Something to upload where only some types are allowed. */
  php: { name: 'e2e-shell.php', make: () => Buffer.from('<?php echo "e2e-upload-executed";\n') },
} satisfies Record<string, { name: string; make: () => Buffer }>;

export type TestFile = keyof typeof GENERATORS;

/** Absolute path of a generated fixture, created on first use. */
export function testFile(kind: TestFile): string {
  const { name, make } = GENERATORS[kind];
  const file = path.join(DIR, name);
  if (!fs.existsSync(file)) {
    fs.mkdirSync(DIR, { recursive: true });
    fs.writeFileSync(file, make());
  }
  return file;
}
