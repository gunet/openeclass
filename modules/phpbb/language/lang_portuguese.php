<?php
/*******************************************************************************
 *                 lang_portuguese.php  -  description
 *                              -------------------
 *     begin                : Sat Mar 10 2001
 *     copyright            : (C) 2000 by Pedro Lopes
 *     email                : phoenix@nets-r-us.org
 *
 *   $Id$
 *        Translation of the original english file by James Atkinson
 *        Tradução do ficheiro original em inglês de James Atkinson
 *
 ******************************************************************************/

/*******************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ******************************************************************************/

$l_forum         = "Forum";
$l_forums        = "Foruns";
$l_topic        = "Tópico";
$l_topics         = "Tópicos";
$l_replies        = "Respostas";
$l_poster        = "Autor";
$l_author        = "Autor";
$l_views        = "Vistas";
$l_post         = "Mensagem";
$l_posts         = "Mensagens";
$l_message        = "Mensagem";
$l_messages        = "Mensagens";
$l_subject        = "Assunto";
$l_body                = "Corpo da $l_message";
$l_from                = "De";   // Message from
$l_moderator         = "Moderador";
$l_username         = "Nome de Utilizador";
$l_password         = "Senha";
$l_email         = "Email";
$l_emailaddress        = "Endereço de Email";
$l_preferences        = "Preferências";

$l_anonymous        = "Anónimo";  // Post
$l_guest        = "Visitante"; // Whosonline
$l_noposts        = "Sem $l_posts";
$l_joined        = "Entrou";
$l_gotopage        = "Ir para a página";
$l_nextpage         = "Próxima Página";
$l_prevpage     = "Página Anterior";
$l_go                = "Ir";
$l_selectforum        = "Seleccione um $l_forum";

$l_date                = "Data";
$l_number        = "Número";
$l_name                = "Nome";
$l_options         = "Opções";
$l_submit        = "Enviar";
$l_confirm         = "Confirmar";
$l_enter         = "Entrar";
$l_by                = "por"; // Posted by
$l_ondate        = "em"; // This message is edited by: $username on $date
$l_new                = "Novas";

$l_html                = "HTML";
$l_bbcode        = "BBcode";
$l_smilies        = "Sorrisos";
$l_on                = "Ligado";
$l_off                = "Desligado";
$l_yes                = "Sim";
$l_no                = "Não";

$l_click         = "Clique";
$l_here         = "aqui";
$l_toreturn        = "para voltar";
$l_returnindex        = "$l_toreturn ao index do forum";
$l_returntopic        = "$l_toreturn à lista de tópicos do forum.";

$l_error        = "Erro";
$l_tryagain        = "Por favor volte e tente novamente.";
$l_mismatch         = "As senhas não combinam.";
$l_userremoved         = "Este utilizador foi removido da base de dados de Utilizadores";
$l_wrongpass        = "Introduziu a senha errada.";
$l_userpass        = "Por favor introduza o seu nome de utilizador e senha.";
$l_banned         = "Foi banido deste forum. Contacte o administrador se tiver quaisquer dúvidas.";
$l_enterpassword = "Tem de introduzir a sua senha.";

$l_nopost        = "Não tem acesso para colocar mensagens neste forum.";
$l_noread        = "Não tem acesso para ler este forum.";

$l_lastpost         = "Última $l_post";
$l_sincelast        = "desde a sua última visita";
$l_newposts         = "Novas $l_posts $l_sincelast";
$l_nonewposts         = "Sem novas $l_posts $l_sincelast";

// Index page
$l_indextitle        = "Index do Forum";

// Members and profile
$l_profile        = "Perfil";
$l_register        = "Registar";
$l_onlyreq         = "Apenas necessário se estiver a ser alterado";
$l_location         = "De";
$l_viewpostuser        = "Ler mensagens deste utilizador";
$l_perday       = "$l_messages por dia";
$l_oftotal      = "do total";
$l_url                 = "URL";
$l_icq                 = "ICQ";
$l_icqnumber        = "Número de ICQ";
$l_icqadd        = "Adicionar";
$l_icqpager        = "Pager";
$l_aim                 = "AIM";
$l_yim                 = "YIM";
$l_yahoo         = "Yahoo Messenger";
$l_msn                 = "MSN";
$l_messenger         = "MSN Messenger";
$l_website         = "Endereço do Web Site";
$l_occupation         = "Ocupação";
$l_interests         = "Interesses";
$l_signature         = "Assinatura";
$l_sigexplain         = "Este é um bloco de texto que pode ser adicionado às mensagens que colocar.<BR>Máximo de 255 caracteres!";
$l_usertaken        = "O $l_username que escolheu já está ocupado.";
$l_userdisallowed = "O $l_username que escolheu foi bloqueado pelo administrador. $l_tryagain";
$l_infoupdated        = "A sua informação foi actualizada";
$l_publicmail        = "Permitir que outras pessoas vejam o meu $l_emailaddress";
$l_itemsreq        = "Items marcados com * são obrigatórios";

// Viewforum
$l_viewforum        = "Ver Forum";
$l_notopics        = "Não existem tópicos neste fórum. Pode colocar um novo.";
$l_hotthres        = "Mais de $hot_threshold $l_posts";
$l_islocked        = "$l_topic está Bloqueado (Não são permitidas novas $l_posts)";
$l_moderatedby        = "Moderado por";

// Private forums
$l_privateforum        = "Este é um <b>Forum Privado</b>.";
$l_private         = "$l_privateforum<br>Nota: Precisa de ter cookies activos para usar os foruns privados.";
$l_noprivatepost = "$l_privateforum Não tem acesso para colocar mensagens nesse forum.";

// Viewtopic
$l_topictitle        = "Ver $l_topic";
$l_unregistered        = "Utilizador Não Registado";
$l_posted        = "Colocado";
$l_profileof        = "Ver Perfil de";
$l_viewsite        = "Ir para o website de";
$l_icqstatus        = "Status do $l_icq ";  // ICQ status
$l_editdelete        = "Editar/Apagar Esta $l_post";
$l_replyquote        = "Responder com citação";
$l_viewip        = "Ver IP do Autor (Apenas Moderadores/Admins)";
$l_locktopic        = "Bloquear este $l_topic";
$l_unlocktopic        = "Desbloquear este $l_topic";
$l_movetopic        = "Mover este $l_topic";
$l_deletetopic        = "Apagar este $l_topic";

// Functions
$l_loggedinas        = "Entrou como";
$l_notloggedin        = "Não entrou";
$l_logout        = "Sair";
$l_login        = "Entrar";

// Page_header
$l_separator        = "» »";  // Included here because some languages have
                              // problems with high ASCII (Big-5 and the like).
$l_editprofile        = "Editar Perfil";
$l_editprefs        = "Editar $l_preferences";
$l_search        = "Procura";
$l_memberslist        = "Lista de Membros";
$l_faq                = "FAQ";
$l_privmsgs        = "$l_messages Privadas";
$l_sendpmsg        = "Envie uma Mensagem Privada";
$l_statsblock   = '$statsblock = "Os nossos utilizadores colocaram um total de -$total_posts- $l_messages.<br>
Temos um total de -$total_users- Utilizadores Registados.<br>
O mais novo Utilizador Registado é -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"utilizador está":"utilizadores estão") ." <a href=\"$online_url\">actualmente a navegar</a> pelos foruns.<br>";';
$l_privnotify   = '$privnotify = "<br>Tem $new_message <a href=\"$privmsg_url\">novas ".($new_message>1?"mensagens":"mensagem")."</a> privadas.";';

// Page_tail
$l_adminpanel        = "Painel de Administração";
$l_poweredby        = "Suportado por";
$l_version        = "Versão";

// Auth

// Register
$l_notfilledin        = "Erro - Não preencheu todos os campos necessários.";
$l_invalidname        = "O nome de utilizador escolhido \"$username\" já está ocupado.";
$l_disallowname        = "O nome de utilizador escolhido, \"$username\" foi bloqueado pelo administrador.";

$l_welcomesubj        = "Bem vindo aos Foruns $sitename";
$l_welcomemail        =
"
$l_welcomesubj,

Por favor guarde este email para sua referência.


A informação da sua conta é a seguinte:

---------------------------------------
Nome de Utilizador: $username
Senha: $password
---------------------------------------

Por favor não se esqueça da sua senha, pois foi encriptada na nossa base de de dados e não podemos recuperá-la...
No entanto, caso a esqueça, providenciamos um formulário simples que irá gerar e enviar por email uma nova senha aleatória.

Obrigado por se registar.

$email_sig
";
$l_beenadded        = "Foi adicionado à base de dados.";
$l_thankregister= "Obrigado por se registar!";
$l_useruniq        = "Precisa de ser único. Dois utilizadores não podem ter o mesmo Nome de Utilizador.";
$l_storecookie        = "Guardar o meu Nome de Utilizador num cookie por 1 ano";

// Prefs
$l_prefupdated        = "$l_preferences actualizadas. $l_click <a href=\"index.$phpEx\">$l_here</a> $l_returnindex";
$l_editprefs        = "Editar as suas $l_preferences";
$l_themecookie        = "NOTA: Para usar os temas TEM que ter os cookies activos.";
$l_alwayssig        = "Anexar sempre a minha assinatura";
$l_alwaysdisable = "Desactivar sempre"; // Only used for next three strings
$l_alwayssmile        = "$l_alwaysdisable $l_smilies";
$l_alwayshtml        = "$l_alwaysdisable $l_html";
$l_alwaysbbcode        = "$l_alwaysdisable $l_bbcode";
$l_boardtheme        = "Tema do Forum";
$l_boardlang    = "Idioma do Forum";
$l_nothemes        = "Nenhum Tema na base de dados";
$l_saveprefs        = "Gravar $l_preferences";

// Search
$l_searchterms        = "Palavras-Chave";
$l_searchany        = "Procurar por QUALQUER das palavras (Default)";
$l_searchall        = "Procurar por TODAS as palavras";
$l_searchallfrm        = "Procurar TODOS os Foruns";
$l_sortby        = "Ordenar por";
$l_searchin        = "Procurar em";
$l_titletext        = "Título & Texto";
$l_search        = "Procurar";
$l_nomatches        = "Nenhuma ocorrência foi encontrada para esta procura. Por favor forneça mais detalhes.";

// Whosonline
$l_whosonline        = "Quem está online?";
$l_nousers        = "Nenhum utilizador está actualmente a navegar nos foruns";


// Editpost
$l_notedit        = "Não pode editar uma mensagem que não é sua.";
$l_permdeny        = "Não forneceu a $l_password correcta ou não tem permissão para editar esta mensagem. $l_tryagain";
$l_editedby        = "Esta $l_message foi editada por:";
$l_stored        = "A sua $l_message foi gravada na base de dados.";
$l_viewmsg        = "para ver a sua $l_message.";
$l_deleted        = "A sua $l_post foi apagada.";
$l_nouser        = "Este $l_username não existe.";
$l_passwdlost        = "Esqueci-me da minha senha!";
$l_delete        = "Apagar esta Mensagem";

$l_disable        = "Desactivar";
$l_onthispost        = "nesta Mensagem";

$l_htmlis        = "$l_html está";
$l_bbcodeis        = "$l_bbcode está";

$l_notify        = "Notificar por email quando for respondida";

// Newtopic
$l_emptymsg        = "Precisa excrever uma $l_message para enviar. Não pode enviar uma $l_message vazia.";
$l_aboutpost        = "Sobre Enviar Mensagens";
$l_regusers        = "Todos os utilizadores <b>registados</b>";
$l_anonusers        = "Utilizadores <b>Anónimos</b>";
$l_modusers        = "Apenas <B>Moderadores e Administradores</b>";
$l_anonhint        = "<br>(Para enviar anónimamente não intruduza um nome de utilizador e senha)";
$l_inthisforum        = "pode colocar novos tópicos e respostas neste forum";
$l_attachsig        = "Mostrar assinatura <font size=-2>(Isto pode ser alterado ou adicionado ao seu perfil)</font>";
$l_cancelpost        = "Cancelar Mensagem";

// Reply
$l_nopostlock        = "Não pode colocar uma resposta neste tópico porque foi bloqueado.";
$l_topicreview  = "Rever Tópico";
$l_notifysubj        = "Foi colocada uma resposta ao seu tópico.";
$l_notifybody        = 'Olá $m[username]\r\nRecebeu este Email porque uma mensagem que
colocou nos Foruns $sitename recebeu uma resposta, e
pediu para ser notificado sobre estes eventos.

Pode ver o tópico em:

http://$SERVER_NAME$url_phpbb/viewtopic.$phpEx?topic=$topic&forum=$forum

Ou ver o Index do Forum $sitename em

http://$SERVER_NAME$url_phpbb

Obrigado por usar os Foruns $sitename.

Cumprimentos,

$email_sig';


$l_quotemsg        = '[quote]\nEm $m[post_time], $m[username] escreveu:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient        = "Tem de introduzir o nome de utilizador para quem quer enviar a $l_message.";
$l_sendothermsg        = "Enviar outra Mensagem Privada";
$l_cansend        = "pode enviar $l_privmsgs";  // All registered users can send PM's
$l_yourname        = "O seu $l_username";
$l_recptname        = "O $l_username do Destinatário";

// Replypmsg
$l_pmposted        = "Resposta aceite, pode clicar <a href=\"viewpmsg.$phpEx\">aqui</a> para ver as suas $l_privmsgs";

// Viewpmsg
$l_nopmsgs        = "Não tem $l_privmsgs.";
$l_reply        = "Responder";

// Delpmsg
$l_deletesucces        = "Apagada correctamente.";

// Smilies
$l_smilesym        = "O que escrever";
$l_smileemotion        = "Emoção";
$l_smilepict        = "Desenho";

// Sendpasswd
$l_wrongactiv        = "A chave de activação fornecida está incorrecta. Por favor verifique a $l_message que recebeu e certifique-se que copiou a chave de activação exacta.";
$l_passchange        = "A sua senha foi alterada correctamente. Pode agora ir para o seu <a href=\"bb_profile.$phpEx?mode=edit\">perfil</a> e mudar a senha para algo mais apropriado.";
$l_wrongmail        = "O endereço de email que introduziu é diferente do gravado na nossa base de dados.";

$l_passsubj        = "Senha alterada nos Foruns $sitename";

$l_pwdmessage        = 'Olá $checkinfo[username],
Recebeu este email porque você (ou alguém)
solicitou uma alteração de senha nos Foruns $sitename. Se
recebeu esta mensagem erroneamente apague-a simplesmente e sua senha permanecerá
a mesma.

A sua senha gerada pelos Foruns é: $newpw

Para esta alteração ser efectuada tem de visitar esta página:

   http://$SERVER_NAME$PHP_SELF?actkey=$key

Quando visitar esta página, a sua senha será alterada na nossa base de dados,
e poderá entrar no seu Perfil e mudá-la conforme desejado.

Obrigado por usar os Foruns $sitename

$email_sig';

$l_passsent        = "A sua senha mudou para uma nova e aleatória. Por favor verifique o seu email para completar o procedimento de alteração.";
$l_emailpass        = "Email da Senha Perdida";
$l_passexplain        = "Por favor preencha o formulário e uma nova senha será enviada para seu endereço de Email";
$l_sendpass        = "Enviar Senha";


?>