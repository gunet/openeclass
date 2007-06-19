@echo off
FOR %%V IN (%*) DO copy %%V %%V_uncompressed.js
FOR %%V IN (%*) DO java -jar %~p0dojo_js_compressor.jar -c %%V_uncompressed.js > %%V 2>&1

FOR %%V IN (%*) DO del %%V_uncompressed.js

# pause