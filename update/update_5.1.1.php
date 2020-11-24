<?php

/**
 * Classe de mise a jour pour PluXml version 5.1.1
 *
 * @package PLX
 * @author    Stephane F
 **/
class update_5_1_1 extends plxUpdate
{

    # Migration du fichier des utilisateurs: renforcement des mots de passe
    public function step1()
    {

        echo L_UPDATE_USERS_MIGRATION . "<br />";

        # On génère le fichier XML
        ob_start();
        ?>
        <document>
            <?php
            foreach ($this->plxAdmin->aUsers as $user_id => $user) {
                $salt = plxUtils::charAleatoire(10);
                $password = sha1($salt . $user['password']);
                ?>
                <user number="<?= $user_id ?>" active="<?= $user['active'] ?>" profil="<?= $user['profil'] ?>"
                      delete="<?= $user['delete'] ?>">
                    <login><?= plxUtils::cdataCheck($user['login']) ?></login>
                    <name><?= plxUtils::cdataCheck($user['name']) ?></name>
                    <infos><?= plxUtils::cdataCheck($user['infos'] ?></infos>
                    <password><?= $password ?></password>
                    <salt><?= $salt ?></salt>
                    <email><?= plxUtils::cdataCheck($user['email']) ?></email>
                    <lang><?= $user['lang'] ?></lang>
                </user>
                <?php
            }
            ?>
        </document>
        <?php
        if (!plxUtils::write(XML_HEADER . ob_get_clean(), PLX_ROOT . $this->plxAdmin->aConf['users'])) {
            echo '<p class="error">' . L_UPDATE_ERR_USERS_MIGRATION . ' (' . $this->plxAdmin->aConf['users'] . ')</p>';
            return false;
        }

        return true;
    }

}

?>
