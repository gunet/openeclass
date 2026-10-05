# Security Policy

## Reporting a Vulnerability

Please do not report security vulnerabilities in public issues or pull requests.

You can report them privately to the contact email of the Open eClass core
development team, `eclass@gunet.gr`.

Alternatively, and especially for more significant vulnerabilities which
warrant issuing a CVE, you can use the **Report a vulnerability** button in the
[Security & quality tab](https://github.com/gunet/openeclass/security) of this
repository. Only you, the repository administrators and the people they add to
the report can see it until it is published.

Please include:
- the affected code (file, view, or URL) and the branch or commit,
- the required user role or permissions, as well as any non-default settings or modules,
- step-by-step reproduction instructions on a development setup (e.g. an HTTP request, payload, or test case),
- the impact and what an attacker gains.

Please reproduce issues on a local development setup, not on production Open
eClass instances. Until the report is published, do not push a fix or a proof of
concept to your fork or open a pull request: forks of this repository are
public.

Please note that a large number of educational institutions run and maintain
Open eClass installations with limited resources, and are often hesitant to
perform major upgrades during active academic terms. For this reason, while
we are thankful for any security and vulnerability reports and strive to fix
all issues in a timely manner, we ask for a flexible, coordinated disclosure
timeline prior to public advisory release, especially regarding critical
vulnerabilities.
