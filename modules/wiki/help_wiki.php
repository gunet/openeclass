<?php // $Id$
    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * CLAROLINE
     *
     * @version 1.7 $Revision$
     *
     * @copyright 2001-2005 Universite catholique de Louvain (UCL)
     *
     * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */
    
    require '../inc/claro_init_global.inc.php';

    $nameTools = $langWiki;
    $hide_banner=TRUE;
    
    $htmlHeadXtra[] =
        '<style type="text/css">
            dt{font-weight:bold;margin-top:5px;}
        </style>';
    
    require_once $includePath."/claro_init_header.inc.php";
    
    $help = ( isset( $_REQUEST['help'] ) ) ? $_REQUEST['help'] : 'syntax';
    
    echo '<center><a href="#" onclick="window.close()">'.$langCloseWindow.'</a></center>' . "\n";
    
    switch( $help )
    {
        case 'syntax':
        {
            echo $langWikiHelpSyntaxContent;
            break;
        }
        case 'admin':
        {
            echo $langWikiHelpAdminContent;
            break;
        }
        default:
        {
            echo '<center><h1>Missing help request</h1></center>';
        }
    }
    
    echo '<center><a href="#" onclick="window.close()">'.$langCloseWindow.'</a></center>' . "\n";
    
    $hide_footer = true;
    require_once $includePath."/claro_init_footer.inc.php";
    
?>