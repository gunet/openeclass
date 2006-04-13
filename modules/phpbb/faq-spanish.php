<?php
/***************************************************************************
                          faq-spanish.php  -  description
                             -------------------
    begin                : Tue April 3, 2001
    copyright            : (C) 2001 Fernando Nájera
    email                : yo@fernandonajera.com

    $Id$

 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
/***************************************************************************
 * Created by: Steven Cunningham (defender@webinfractions.com) for phpBB
 * *************************************************************************/
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "FAQ";
$pagetype = "other";
include('page_header.'.$phpEx);
?>

<div align="center"><center>
<table border="0" width="<?php echo $tablewidth?>" bgcolor="<?php echo $table_bgcolor?>">
  <TR><TD>
<table border="0" width="100%" bgcolor=>
    <tr bgcolor="<?php echo $color1?>">
        <td><font size="<?php echo $FontSize4?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>"><b>Preguntas Frecuentemente
          Contestadas</font></b></td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
          <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $linkcolor?>">
          <a href="#register">¿Tengo que registrarme?</a><br>
          <a href="#smilies">¿Puedo usar smilies?</a><br>
          <a href="#html">Usando HTML</a><br>
          <a href="#bbcode">Usando BB Code</a><br>
          <a href="#mods">¿Qué son los moderadores?</a><br>
	  <a href="#profile">¿Puedo cambiar mi perfil?</a><br>
          <a href="#prefs">¿Puedo personalizar el panel de alguna forma?</a><br>
          <a href="#cookies">¿Se usan cookies?</a><br>
          <a href="#edit">¿Puedo editar mis propios mensajes?</a><br>
          <a href="#attach">¿Puedo incluir ficheros?</a><br>
          <a href="#search">¿Cómo puedo buscar?</a><br>
          <a href="#signature">¿Puedo añadir una firma al final de mis mensajes?</a><br>
	  <a href="#announce">¿Qué son los anuncios?</a><br>
          <a href="#pw">¿Existe un sistema de recuperación de nombre de usuario/clave?</a><br>
          <a href="#notify">¿Puedo ser notificado por email si alguien responde a mi tema?</a><br>
          <a href="#searchprivate">¿Puedo buscar en los foros privados?</a><br>
          <a href="#ranks">¿Qué son los ránkings en los Foros <?php echo $sitename?>?</a><br>
          <a href="#rednumbers">¿Por qué hay iconos llameantes en la vista de temas?</a></p></font>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
        <font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
        <a name="register"><b><br>Registrándose</b></font></a>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	El registro sólo se necesita en base a cada foro. Dependiendo de cómo
	el administrador haya preparado sus foros, algunos pueden obligarte a
	que te registres para poder escribir, mientras que otros pueden permitirte
	escribir de forma anónima. Si se permite la escritura anónima, puedes
	hacerlo símplemente no introduciendo un nombre de usuario y una clave
	cuando se te pregunte.
	El registro es gratuíto, y no es necesario poner el nombre real.
	Tampoco es necesario poner tu dirección real de correo electrónico, sin embargo,
	sólo se usará para enviarte por email una nueva clave si has perdido la tuya.
	También tienes la opción de ocultar tu dirección de correo electrónico de todo
	el mundo excepto del administración; esta opción está seleccionada por defecto,
	pero puedes permitir a los demás ver tu dirección de correo electrónico
	seleccionando la casilla 'Permitir que otros vean mi Dirección de correo
	electrónico' en el formulario de registro.
	Puedes registrarte pulsando
	<a href="<?php echo $url_phpbb?>/bb_register.<?php echo $phpEx?>?mode=agreement">aquí</a></font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="smilies">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
        <b>Smilies</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Probablemente hayas visto a otros usar smilies antes en los mensajes de correo electrónico,
	o en otros mensajes en algún sistema de foros. Los smilies son caracteres del teclado que se usan para
	transmitir una emoción, como una sonrisa
	:)
	o un enfado
	:(.
	Este sistema de foros automáticamente convierte ciertos smilies a una representación gráfica.
        Actualmente se traducen los siguientes smilies: </font><BR>
	<table width="50%" ALIGN="CENTER" BGCOLOR="<?php echo $table_bgcolor?>" CELLSPACEING=1 BORDER="0">
	  <TR><TD>
	  <TABLE WIDTH="100%" BORDER="0">
		 <TR BGCOLOR="<?php echo $color1?>">
		 <TD width="100">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		 		<?php echo $l_smilesym?>
		 	</FONT>
		 </td>
		 <td width="50%">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo $l_smileemotion?>
			</FONT>
		</td>
		<td width="55">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo $l_smilepict?>
			</FONT>
		</td></tr>
 <?php

	  if ($getsmiles = mysql_query("SELECT * FROM smiles")) {
	     while ($smile = mysql_fetch_array($getsmiles)) {
?>
		 <TR BGCOLOR="<?php echo $color2?>">
		 <TD width="100">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		 		<?php echo stripslashes($smile[code])?>
		 	</FONT>
		 </td>
		 <td width="50%">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo stripslashes($smile[emotion])?>&nbsp;
			</FONT>
		</td>
		<td width="55">
			<IMG SRC="<?php echo "$url_smiles/$smile[smile_url]";?>">
		</td></tr>
<?php
	     }
	  } else
	     echo "Could not retrieve from the smile database.";
?>
    </TABLE></TABLE>
    </div>
	</td>
    </tr>
	<tr bgcolor="<?php echo $color1?>">
	<td>
		<p align="left"><a name="html">
		<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<b>Usando HTML</b></font></a></p>
	</td>
	</tr>
	<tr bgcolor="<?php echo $color2?>">
	<td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Es posible que puedas usar HTML en tus mensajes, si los
	administradores y los moderadores han activado esta opción.
	Cada vez que escribes un mensaje nuevo, se te dirá si se permiten códigos HTML o BB Code.
	Si HTML está activado, puedes usar cualquier tag HTML, pero por favor ten cuidado de
	usar una sintaxis HTML correcta. Si no lo haces, tu moderador o administrador quizás
	tengan que editar tu mensaje.
	</td>
	<tr bgcolor="<?php echo $color1?>">
	<td>
		<p align="left"><a name="bbcode">
		<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<b>Usando BB Code</b></font></a></p>
	</td>
	</tr>
	<tr bgcolor="<?php echo $color2?>">
	<td>

BBCode es una variación a los tags de HTML que quizás ya te resulten familiares. Básicamente, permite añadirle funcionalidad o estilo a tu mensaje, lo que normalmente requeriría HTML. Puedes usar BBCode incluso si el HTML no se permite en el foro que estás usando. Puede que desees usar BBCode en vez de HTML, incluso si el HTML se permite en tu foro, porque se necesita menos código y es más seguro de usar (la sintaxis de codificación incorrecta no lleva a tantos problemas).
<P>

<table border=0 cellpadding=0 cellspacing=0 width="<?php echo $tablewidth?>" align="CENTER"><TR><td bgcolor="#FFFFFF">
<table border=0 cellpadding=4 border=0 cellspacing=1 width=100%>
<TR bgcolor="<?php echo $color1?>">
<TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Hipervínculos URL</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD><FONT SIZE="2" FACE="Verdana, Arial">
Si BBCode está permitido en un foro, no necesitas usar más el código [URL] para crear un hipervínculo. Simplemente escribe la dirección completa en cualquiera de las siguientes formas, y el hipervínculo se creará automáticamente:
<UL><FONT SIZE="2" FACE="Verdana, Arial" color="silver">
<LI> http://www.tuURL.com
<LI> www.tuURL.com
</font>

Ten en cuenta que puedes o bien usar la dirección completa con http:// o acortarla al dominio www.  Si el vínculo no empieza con "www", entonces debes usar la dirección completa "http://". Por otra parte, puedes usar los prefijos de URL https y ftp en el modo auto-vínculo (cuando BBCode está ACTIVADO).
<P>
El viejo código [URL] todavía funciona, como se detalla a continuación.

Basta con intercalar el vínculo como se muestra en el siguiente ejemplo (el BBCode está en <FONT COLOR="#FF0000">rojo</FONT>).
<P><center>
<FONT COLOR="#FF0000">[url]</FONT>www.totalgeek.org<FONT COLOR="#FF0000">[/url]</FONT>
<P></center>
También puedes poner verdaderos hipervínculos usando el código [url].  Basta con usar el siguiente formato:
<BR><center>
<FONT COLOR="#FF0000">[url=http://www.totalgeek.org]</font>totalgeek.org<FONT COLOR="#FF0000">[/url]</font>
</center><p>
En los ejemplos anteriores, el BBCode automáticamente genera un hipervínculo a la dirección URL que se indica. También se asegura de que el vínculo se abre en una nueva ventana cuando el usuario pulse en ella. Fíjate que la parte "http://" del URL es completamente opcional. En el segundo ejemplo de arriba, el URL hará un enlace a cualquier URL que pongas después del signo igual. Por otra parte, NO debería usar comillas dentro del tag URL.
</font>
</td>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Vínculos de Email</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Para añadir un vínculo a una dirección email dentro de un mensaje, basta intercalarlo como muestra el siguiente ejemplo (el BBCode está en <FONT COLOR="#FF0000">rojo</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[email]</FONT>james@totalgeek.org<FONT COLOR="#FF0000">[/email]</FONT>
</CENTER>
<P>
En el ejemplo anterior, el BBCode automáticamente genera un hipervínculo a la dirección email que está intercalada.
</FONT>
</td></tr>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Negritas y Cursivas</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Puedes hacer texto en cursiva o en negrita al intercalar las secciones apropiadas de tu texto entre los tags [b] [/b] o [i] [/i].
<P>
<CENTER>
Hola, <FONT COLOR="#FF0000">[b]</FONT><B>Juan</B><FONT COLOR="#FF0000">[/b]</FONT><BR>
Hola, <FONT COLOR="#FF0000">[i]</FONT><I>María</I><FONT COLOR="#FF0000">[/i]</FONT>
</CENTER>
</FONT>
</td></tr>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Viñetas/Listas</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Puedes hacer listas con viñetas o listas ordenadas (por letras o números).
<P>
Las listas no ordenadas y con viñetas:
<P>
<FONT COLOR="#FF0000">[list]</FONT>
<BR>
<FONT COLOR="#FF0000">[*]</font> Este es el primer ítem en la lista.<BR>
<FONT COLOR="#FF0000">[*]</font> Este es el segundo ítem en la lista.<BR>
<FONT COLOR="#FF0000">[/list]</font>
<P>
This produces:
<ul>
<LI> Este es el primer ítem en la lista.
<LI> Este es el segundo ítem en la lista.
</ul>
Observa que debes incluir un [/list] cuando termines cada lista.

<P>
Hacer listas ordenadas es igual de fácil. Basta añadir o [LIST=A] o [LIST=1].  Escribiendo [List=A] producirá una lista de la A a la Z.  Usando [List=1] producirá listas numeradas.
<P>
Aquí hay un ejemplo:
<P>

<FONT COLOR="#FF0000">[list=A]</FONT>
<BR>
<FONT COLOR="#FF0000">[*]</font> Este es el primer ítem en la lista.<BR>
<FONT COLOR="#FF0000">[*]</font> Este es el segundo ítem en la lista.<BR>
<FONT COLOR="#FF0000">[/list]</font>
<P>
Esto produce:
<ol type=A>
<LI> Este es el primer ítem en la lista.
<LI> Este es el segundo ítem en la lista.
</ul>


</FONT>
</td></tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Añadiendo imágenes</font></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Para añadir un gráfico en tu mensaje, basta poner el URL de la imagen gráfica como se muestra en el siguiente ejemplo (el BBCode está en <FONT COLOR="#FF0000">rojo</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[img]</FONT>http://www.totalgeek.org/images/tline.gif<FONT COLOR="#FF0000">[/img]</FONT>
</CENTER>
<P>
En el ejemplo anterior, el BBCode automáticamente hace visible el gráfico en tu mensaje. Nota: la parte "http://" de la URL es NECESARIA para el código <FONT COLOR="#FF0000">[img]</FONT>.
</FONT>
</td></tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Citando otros mensajes</font></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Para referirse a algo específico que otro ha escrito, basta cortar y pegar el texto adecuado y encerrarlo como se muestra a continuación (el BBCode está en <FONT COLOR="#FF0000">rojo</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[QUOTE]</FONT>No preguntes lo que tu país puede hacer por tí....<BR>pregúntate qué puedes hacer por tu país.<FONT COLOR="#FF0000">[/QUOTE]</FONT>
</CENTER>
<P>
En el ejemplo anterior, el BBCode automáticamente prepara el texto que se indica.</FONT>
</td>
</tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Tag de código</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Similar al tag de Cita, el tag de Código añade algunos tags &lt;PRE&gt; para preservar el formato. Esto es útil para mostrar código de programación, por ejemplo.
<P>

<FONT COLOR="#FF0000">[CODE]</FONT>#!/usr/bin/perl
<P>
print "Content-type: text/html\n\n";
<BR>
print "¡Hola mundo!";
<FONT COLOR="#FF0000">[/CODE]</FONT>

<P>
En el ejemplo anterior, el BBCode automáticamente prepara el texto que se indica y preserva el formato del texto de código.</FONT>
</td>
</tr>
</table>
</td></tr></table>
</blockquote>
<BR>
No debes usar los dos sistemas (HTML y BBCode) para hacer la misma función. Fíjate que los BBCode no distinguen mayúsculas y minúsculas (por tanto, puedes usar <FONT COLOR="#FF0000">[URL]</FONT> o <FONT COLOR="#FF0000">[url]</FONT>).
<P>
<FONT COLOR="silver">Uso Incorrecto de BBCode:</FONT>
<P>
<FONT COLOR="#FF0000">[url]</FONT> www.totalgeek.org <FONT COLOR="#FF0000">[/url]</FONT> - no pongas ningún espacio entre el código entre corchetes y el texto al que se aplica el código.
<P>
<FONT COLOR="#FF0000">[email]</FONT>james@totalgeek.org<FONT COLOR="#FF0000">[email]</FONT> - los corchetes del final deben incluir una barra hacia adelante (/) (<FONT COLOR="#FF0000">[/email]</FONT>)

<P>
</FONT>
</B>

	</td>


    <tr bgcolor="<?php echo $color1?>">
        <td nowrap>
	<p align="left"><a name="mods">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Moderadores</b></font></a></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
          <p>
	    <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	    Los moderadores controlan los foros individuales.
            Ellos pueden editar, borrar o pasa cualquier escrito en su foro.
            Si tienes alguna pregunta sobre un foro en particular, deberías dirigirte al moderador de tu foro.</p>
          <p>Los administradorse y los moderadores de foros se reservan el derecho de terminar o borrar cualquier escrito que no tengan un
	    tema claro y acorde al topic. Hay muchos miembros que todavía usan
	    módems de 28.8 y de 56k que no tienen tiempo para bucear a través
	    de temas poco útiles y sin sentido. </p>
          <p>Cualquiera que escriba para incrementar sus estadísticas en los Foros de <?php echo $sitename?> o escriba sobre temas fuera de lugar corren el riesgo de que se cierren sus temas, se eliminen y/o se cancele su pertenencia a los foros. </p>
          <p>Intente hacer que el tema sea un reflejo de lo que hay dentro del hilo. Temas como "Comprueba esto!" y ""~~\\¡Tienes que ver esto!//~~" sólo atraen a los miembros a un tema que puede que no quieran leer.</font></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<p align="left"><a name="profile">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Cambiando tu perfil</b></font></a></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Puedes cambiar fácilmente cualquier información almacenada en tu perfil de registro, usando el vínculo
        &quot;Editar Perfil&quot; en la parte superior de cada página.
        Simplemente identifícate escribiendo tu nombre de usuario y tu clave,
	o entrando en el sistema, y toda la información de tu perfil
	aparecerá en pantalla.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="prefs">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Personalizando usando preferencias</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Como usuario registrado del sistema de foros,
	puedes almacenar tu nombre de usuario en la memoria durante un año como máximo.
	Al hacer esto nosotros creamos una forma de seguir la pista de quién eres cuando visitas los foros, por tanto tú puedes personalizar la apariencia del foro
	al seleccionar de entre los temas que la administración ha dispuesto. Además, si el administrador te lo permite, puedes tener la opción de crear nuevos temas para los foros. Al crear un nuevo tema podrás fijar los colores, fuentes y tamaños de letras del tablón, sin embargo,
	por ahora sólo el administrador puede cambiar las imágenes de cada tema. Cuando un usuario crea un tema las imágenes del tema por defecto se seleccionarán.
	<br>*NOTA: Para poder usar temas DEBES tener las cookies habilitadas.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="cookies">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Cookies</b></font></a></td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Este sistema de foros usa cookies para almacenar la siguiente información:
        la última vez que has visitado los foros, tu nombre de usuario,
	y un número de ID de sesión único cuando entras al sistema. Estas cookies se guardan en tu navegador.
	Si tu navegador no soporta cookies,
	o has seleccionado no habilitar cookies en tu navegador, ninguna de estas
	características que ahorran tiempo funcionarán correctamente. </font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="edit">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Editando tus mensajes</b></font></a>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Puedes editar tus propios mensajes en cualquier momento. Sólo hace falta ir al hilo donde está el mensaje a ser editado, y
	verás un icono de edición en la linea debajo de tu mensaje.
        Pulsa este icono y edita el mensaje. Nadie más puede editar tu mensaje,
	excepto el moderador del foro o el administrador del sistema. Además, durante los 30 minutos siguientes a haber escrito el mensaje, la pantalla de edición te dará la opción de borrar ese mensaje.
	Después de 30 minutos, sin embargo, sólo el moderador y/o el administrador podrán eliminar el mensaje.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="signature">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Añadir firmas</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Puedes usar una firma en tus mensajes.
	Si pulsas en el vínculo de perfil en la parte superior de la
	mayoría de las páginas, podrás editar tu perfil,
	incluyendo tu firma estándar. Una vez que
	hayas almacenado una firma, puedes elegir incluirla en cualquier
	mensaje que crees pulsando la casilla de selección 'Incluir firma' cuando crees un mensaje.
	El administrador de este sistema de foros puede elegir deshabilitar la
	firma en cualquier momento, sin embargo. Si este es el caso, la
	opción 'Incluir firma' no aparecerá cuando se escribe un mensaje, incluso
	si has guardado una firma. Puedes cambiar tu firma en cualquier momento
	cambiando tu perfil.
	<p>Nota: Puedes usar HTML o <a href="#bbcode">BB Code</a> si el administrador ha
	habilitado estas opciones.
	    </font>
        </p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="attach">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Adjuntando ficheros</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Por razones de seguridad, no puedes adjuntar ficheros a ningún
	mensaje. Puedes cortar y pegar texto en tu mensaje, sin embargo,
	o usar HTML y/o BB Code (si se permiten) para dar hipervínculos
	a documentos externos. Los ficheros adjuntos se incluirán en una
	versión futura de phpBB..</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="search">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Buscando un mensaje específico</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Puedes buscar mensajes específicos basados en una o varias palabras presentes
	en el texto, un nombre de usuario, una fecha, y/o uno o varios foros.
	Basta pulsar en el vínculo 'Buscar' en la parte superior de la mayoría de las
	páginas.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="announce">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Anuncios</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Los anuncios no se han implementado, pero están planeados para una versión futura.
	Sin embargo, el administrador puede crear un foro donde sólo puedan escribir otros administradores y moderadores. Este tipo de foros pueden usarse fácilmente como un foro de anuncios.
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="pw">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Nombre de usuario y/o contraseña perdidos</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
 	En el caso de que pierdas tu clave puedes pulsar en el vínculo &quot;¡Olvidé mi
	contraseña!&quot; que se muestra en las pantallas donde se pide la clave, después de este campo. Este vínculo
	te llevará a una página donde puedes rellenar tu nombre de usuario y tu email.
	El sistema te enviará entonces una contraseña aleatoria nueva a la dirección email que se almacena en tu perfil,
	suponiendo que has introducido la dirección de correo electrónica correcta.</FONT>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="notify">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Notificación por email</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Si creas un nuevo tema, tienes la opción de recibir una notificación por email cada vez que alguien escribe una respuesta en ese tema.
	Basta con marcar la casilla de notificación por email en la pantalla de nuevo tema
	(&quot;New Topic&quot;) cuando estás creando tu nuevo tema si quieres
	usar esta característica. </font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="searchprivate">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>¿Puedo buscar en foros privados?</b>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Sí, pero no podrás leer ninguno de los mensajes a menos que tengas la clave del foro privado. </font></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="ranks">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>¿Qué son los rankings de los Foros de <?php echo $sitename?>?</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Los foros de <?php echo $sitename?> han establecido métodos para
	clasificar a sus usuarios a través del número de mensajes escritos.</p>
	<br>
	El ranking actual es el siguiente:<br>

	<?php
	$sql = "SELECT * FROM ranks WHERE rank_special = 0";
	if(!$r = mysql_query($sql, $db)) {
	echo "Error connecting to the database";
	include('page_tail.'.$phpEx);
	exit();
	}
	?>
	<br><TABLE BORDER="0" WIDTH="<?php echo $TableWidth?>" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP"><TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Rank Title&nbsp;</font></TD>
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Minimum Posts&nbsp;</font></TD>
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Maximum Posts&nbsp;</font></TD>
        <TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Rank Image&nbsp;</font></TD>
	</TR>
	<?php
	if($m = mysql_fetch_array($r)) {
	do {
	echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_title]</font></TD>";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_min]</font></TD>";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_max]</font></TD>";
	// The rank image has not been implemented at this time.
        if($m[rank_image] != '')
	   echo "<TD><img src=\"$url_images/$m[rank_image]\"></TD>";
	else
	   echo "<TD>&nbsp;</TD>";
	echo "</TR>";
	} while($m = mysql_fetch_array($r));
	}
	else {
	echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">";
	echo "<TD COLSPAN=\"4\">No Ranks in the database</TD>";
	echo "</TR>";
	}
	?>
	</TABLE></TABLE></font>
	<br>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	El administrador también tiene la opción de asignar rankings especiales a cualquier usuario que escoja. La tabla anterior no muestra estos rankings especiales.
	</font>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="rednumbers">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>¿Por qué algunos iconos </b>
	</font>
	<font color="#FF0033" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>están ardiendo</b>
	</font>
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b> en la vista del foro?</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Los iconos llameantes significan que hay <?php echo $hot_threshold?> o más mensajes en ese hilo.
	Es una advertencia para las conexiones lentas de que el hilo
	puede llevar cierto tiempo para cargarse.</font></p>
        </td>
    </tr>
</table>
</TABLE>
</center>
</div>

<?php
include('page_tail.'.$phpEx);
?>
