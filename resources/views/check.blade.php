@extends('layouts.default')

@section('content')
    <div id="checkcontainer">
        <div style="font-size: 10pt; text-align: justify; margin-left: 20px; margin-right: 20px;">
            <p style="font-size: 10pt; font-weight: bold;">{{ trans('langCheckTools') }}</p>
            <br>
            <p>{{ trans('langCheckIntro') }}</p>
            <br>
            <p>{{ trans('langCheckIntro2') }}</p>
            <br>
            <table class="table-default">
                <tbody>
                    <tr>
                        <th width="50%">
                            <strong>{{ trans('langSoftware') }}</strong>
                        </th>
                        <th width="50%">
                            <strong>{{ trans('langCheck') }}</strong>
                        </th>
                    </tr>
                    <tr class="even">
                        <td>
                            <span style="font-size: 9pt;">
                                <strong>{{ trans('langBrowser') }}:</strong>
                            </span>
                            <div class="divmenu">
                                <ul class="menu">
                                    <li class="mie">{{ trans('langIE') }}</li>
                                    <li class="mfir">{{ trans('langFirefox') }}</li>
                                    <li class="msaf">{{ trans('langSafari') }}</li>
                                    <li class="mchr">{{ trans('langChrome') }}</li>
                                    <li class="moper">{{ trans('langOpera') }}</li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <p id="browsersupported" style="display: none; color: green; font-size: 11px;">
                                <img src="template/default/img/tick.png" border="0" alt="browser supported"> 
                                {{ trans('langBrowserSupported') }}
                            </p>
                            <p id="browsernotsupported" style="display: none; color: red; font-size: 11px;">
                                <img src="template/default/img/delete.png" border="0" alt="browser not supported"> 
                                {{ trans('langBrowserNotSupported') }}:
                                <br><br>
                                <a href="http://www.microsoft.com/windows/internet-explorer/worldwide-sites.aspx" target="_blank">Internet Explorer</a>
                                <br>
                                <a href="http://www.mozilla.org" target="_blank">Mozilla Firefox</a>
                                <br>
                                <a href="http://www.apple.com/safari/" target="_blank">Safari</a>
                                <br>
                                <a href="http://www.google.com/chrome/eula.html" target="_blank">Chrome</a>
                            </p>
                        </td>
                    </tr>
                    <tr class="odd">
                        <td>
                            <span style="font-size: 9pt;">
                                <strong>PDF Reader:</strong>
                            </span>
                            <div class="divpdfmenu">
                                <ul class="pdfmenu">
                                    <li class="mpdf">{{ trans('langAcrobatReader') }}</li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <p id="acrobatreaderinstalled" style="display: none; color: green; font-size: 11px;">
                                <img src="template/default/img/tick.png" border="0" alt="acrobat reader installed"> 
                                {{ trans('langAcrobatReaderInstalled') }}
                            </p>
                            <p id="acrobatreadernotinstalled" style="display: none; color: red; font-size: 11px;">
                                <img src="template/default/img/delete.png" border="0" alt="acrobat reader not installed"> 
                                {{ trans('langAcrobatReaderNotInstalled') }}
                                <a href="http://get.adobe.com/reader/" target="_blank">{{ trans('langHere') }}</a>.
                                {{ trans('langAgreeAndInstall') }}
                            </p>
                        </td>
                    </tr>
                    <tr class="even">
                        <td>
                            <span style="font-size: 9pt;">
                                <strong>Video player:</strong>
                            </span>
                            <div class="divfmenu">
                                <ul class="fmenu">
                                    <li class="mflash">{{ trans('langFlashPlayer') }}</li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <p id="flashplayerinstalled" style="display: none; color: green; font-size: 11px;">
                                <img src="template/default/img/tick.png" border="0" alt="flash player installed"> 
                                {{ trans('langFlashPlayer') }}
                            </p>
                            <p id="flashplayernotinstalled" style="display: none; color: red; font-size: 11px;">
                                <img src="template/default/img/delete.png" border="0" alt="flash player not installed"> 
                                {{ trans('langFlashPlayerNotInstalled') }} 
                                <a href="http://get.adobe.com/flashplayer/" target="_blank">{{ trans('langHere') }}</a>.
                                {{ trans('langAgreeAndInstall') }}
                            </p>
                        </td>
                    </tr>
                    <tr class="odd">
                        <td>
                            <span style="font-size: 9pt;">
                                <strong>Multimedia player:</strong>
                            </span>
                            <div class="divfmenu">
                                <ul class="smenu">
                                    <li class="sflash">Adobe Shockwave Player</li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <p id="shockinstalled" style="display: none; color: green; font-size: 11px;">
                                <img src="template/default/img/tick.png" border="0" alt="shockwave installed"> 
                                {{ trans('langShockInstalled') }}
                            </p>
                            <p id="shocknotinstalled" style="display: none; color: red; font-size: 11px;">
                                    <img src="template/default/img/delete.png" border="0" alt="shockwave not installed"> 
                                    {{ trans('langShockNotInstalled') }}
                                    <a href="http://get.adobe.com/shockwave/" target="_blank">{{ trans('langHere') }}</a>.
                                    {{ trans('langAgreeAndInstall') }}
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br><br>
            <div id="notOK" style="text-align: justify;">
                <div style="text-align: justify;">
                    <strong>{{ trans('langCheckNotOk1') }}</strong>
                </div>
                <div style="text-align: justify;">
                    <ol>
                        <li class="myLi">{{ trans('langCheckNotOk2') }}</li>
                        <li class="myLi">{{ trans('langCheckNotOk3') }}</li>
                        <li class="myLi">
                            {{ trans('langCheckNotOk4') }} 
                            <a href="check.php">{{ trans('langHere') }}</a>.
                            {{ trans('langCheckNotOk5') }}
                        </li>
                    </ol>
                </div>
            </div>
            <p id="OK" style="display: none; text-align: justify;">
                <strong>{{ trans('langCheckOk') }}</strong>
            </p>
            <br>
        </div>
    </div>
@endsection
