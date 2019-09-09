<?php

/**
 * Authentification to admin panel
 * @package PLX
 * @author	Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

namespace controllers;

class AuthController extends AdminController {

    public function __construct(){
        // This page don't need user authentification
        $this->setAuthPage(true);

        parent::__construct();
    }

    /**
     * Index action default view call
     * @author Pedro "P3ter" CADETE
     */
    public function indexAction($connected = null) {
        $plxAdmin = $this->getPlxAdmin();
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxLayoutDir = $this->getPlxAdmin()->getViewsLayoutDir();
        $lang = $plxAdmin->getPlxConfig()->getConfiguration('default_lang');
        $charset = $this->getConfig()->getConfigIni('PLX_CHARSET');

        if($connected) {
            unset($_SESSION['maxtry']);
            $this->getPlxUtils()->cleanHeaders();
            header('Location: '.htmlentities($redirect));
            exit;
        } else if (!$connected){
            $msg = L_ERR_WRONG_PASSWORD;
            $css = 'alert--danger';
        }

        # Form token control
        $this->getPlxToken()->validateFormToken($_POST);

        # brut force protection
        $maxlogin['counter'] = 99; # nombre de tentative de connexion autorisé dans la limite de temps autorisé
        $maxlogin['timer'] = 3 * 60; # temps d'attente limite si nombre de tentative de connexion atteint (en minutes)

        # Alert message initialisation
        $msg = '';
        $css = '';

        # Hook Plugins
        eval($plxAdmin->plxPlugins->callHook('AdminAuthPrepend'));

        # Authentification error
        if(isset($_SESSION['maxtry'])) {
            if( intval($_SESSION['maxtry']['counter']) >= $maxlogin['counter'] AND (time() < $_SESSION['maxtry']['timer'] + $maxlogin['timer']) ) {
                # écriture dans les logs du dépassement des 3 tentatives successives de connexion
                @error_log("PluXml: Max login failed. IP : ".$this->getPlxUtils()->getIp());
                # message à affiche sur le mire de connexion
                $msg = sprintf(L_ERR_MAXLOGIN, ($maxlogin['timer']/60));
                $css = 'alert--danger';
            }
            if( time() > ($_SESSION['maxtry']['timer'] + $maxlogin['timer']) ) {
                # on réinitialise le control brute force quand le temps d'attente limite est atteint
                $_SESSION['maxtry']['counter'] = 0;
                $_SESSION['maxtry']['timer'] = time();
            }
        } else {
            # initialisation de la variable qui compte les tentatives de connexion
            $_SESSION['maxtry']['counter'] = 0;
            $_SESSION['maxtry']['timer'] = time();
        }

        # Incrémente le nombre de tentative
        $redirect=$plxAdmin->aConf['racine'].'/admin/';
        if(!empty($_GET['p']) AND $css=='') {
            
            # on incremente la variable de session qui compte les tentatives de connexion
            $_SESSION['maxtry']['counter']++;
            
            $racine = parse_url($plxAdmin->aConf['racine']);
            $get_p = parse_url(urldecode($_GET['p']));
            $css = (!$get_p OR (isset($get_p['host']) AND $racine['host']!=$get_p['host']));
            if(!$css AND !empty($get_p['path']) AND file_exists('core/admin/'.basename($get_p['path']))) {
                # filtrage des parametres de l'url
                $query='';
                if(isset($get_p['query'])) {
                    $query=strtok($get_p['query'],'=');
                    $query=($query[0]!='d'?'?'.$get_p['query']:'');
                }
                # url de redirection
                $redirect=$get_p['path'].$query;
            }
        }

        # Display the view
        $this->getPlxUtils()->cleanHeaders();
        require_once $this->getPlxAdmin()->getViewsScriptsDir() . 'authView.php';
    }

    /**
     * Authentication action
     * @author Stephane F, Pedro "P3ter" CADETE
     */
    public function loginAction() {
        $plxAdmin = $this->getPlxAdmin();
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxLayoutDir = $this->getPlxAdmin()->getViewsLayoutDir();
        
        if(!empty($_POST['login']) AND !empty($_POST['password']) AND $css=='') {
            $connected = false;
            foreach($plxAdmin->aUsers as $userid => $user) {
                if ($_POST['login']==$user['login'] AND sha1($user['salt'].md5($_POST['password']))===$user['password'] AND $user['active'] AND !$user['delete']) {
                    $_SESSION['user'] = $userid;
                    $_SESSION['profil'] = $user['profil'];
                    $_SESSION['hash'] = $this->getPlxUtils()->charAleatoire(10);
                    $_SESSION['domain'] = $session_domain;
                    # on définit $_SESSION['admin_lang'] pour stocker la langue à utiliser la 1ere fois dans le chargement des plugins une fois connecté à l'admin
                    # ordre des traitements:
                    # page administration : chargement fichier prepend.php
                    # => creation instance plxAdmin : chargement des plugins, chargement des prefs utilisateurs
                    # => chargement des langues en fonction du profil de l'utilisateur connecté déterminé précédemment
                    $_SESSION['admin_lang'] = $user['lang'];
                    $connected = true;
                    break;
                }
            }

            # Call the default action to display the view
            $this->indexAction($connected);
        }
    }

    /**
     * Logout action
     * @author Stephane F, Pedro "P3ter" CADETE
     */
    public function logoutAction() {
        $plxAdmin = $this->getPlxAdmin();
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxLayoutDir = $this->getPlxAdmin()->getViewsLayoutDir();

        $formtoken = $_SESSION['formtoken']; # sauvegarde du token du formulaire
        $_SESSION = array();
        session_destroy();
        session_start();
        $msg = L_LOGOUT_SUCCESSFUL;
        $_SESSION['formtoken'] = $formtoken; # restauration du token du formulaire
        unset($formtoken);

        # Display the view
        $this->getPlxUtils()->cleanHeaders();
        require_once $this->getPlxAdmin()->getViewsScriptsDir() . 'authView.php';
    }

    /**
     * Lost password form action
     * @author Pedro "P3ter" CADETE
     */
    public function lostPasswordAction() {
        $plxAdmin = $this->getPlxAdmin();
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxLayoutDir = $this->getPlxAdmin()->getViewsLayoutDir();

        # Call the default action to display the view
        $this->indexAction();
    }

    /**
     * Send lost possword e-mail action
     * @author Pedro "P3ter" CADETE
     */
    public function sendLostPasswordEmailAction() {
        $plxAdmin = $this->getPlxAdmin();
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxLayoutDir = $this->getPlxAdmin()->getViewsLayoutDir();
        
        if(!empty($_POST['lostpassword_id'])) {
            if (!empty($plxAdmin->sendLostPasswordEmail($_POST['lostpassword_id']))) {
                $msg = L_LOST_PASSWORD_SUCCESS;
                $css = 'alert--success';
            }
            else {
                @error_log("Lost password error. ID : ".$_POST['lostpassword_id']." IP : ".$this->getPlxUtils()->getIp());
                $msg = L_UNKNOWN_ERROR;
                $css = 'alert--danger';
            }
        }

        # Call the default action to display the view
        $this->indexAction($connected);
    }

    /**
     * Change password action
     * @author Pedro "P3ter" CADETE
     */
    public function changePasswordAction() {
        $plxAdmin = $this->getPlxAdmin();
        $plxUtils = $this->getPlxUtils();
        $plxToken = $this->getPlxToken();
        $plxLayoutDir = $this->getPlxAdmin()->getViewsLayoutDir();
        
        if(!empty($_POST['editpassword'])) {
            unset($_SESSION['error']);
            unset($_SESSION['info']);
            $plxAdmin->editPassword($_POST);
            if (!empty($msg = $_SESSION['error'])) {
                $css = 'alert--danger';
            }
            else {
                if (!empty($msg = $_SESSION['info'])) {
                    $css = 'alert--success';
                }
            }
            unset($_SESSION['error']);
            unset($_SESSION['info']);
        }

        # Call the default action to display the view
        $this->indexAction($connected);
    }
}