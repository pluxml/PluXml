#!/bin/sh

# lang.sh KEY-To-Change key-for-replacement keys-to-drop
# Ex: ./lang.sh L_FEED_COMMENTS L_COMMENTS '(L_FEED_COMMENTS|L_MENU_COMMENTS)'
# Ex: ./lang.sh L_AWAITING L_AWAITING L_ALL_AWAITING_MODERATION

GENUINE="$1"
REPLACE="$2"
DROP_LIST="$3"

# On renomme la clé d'origine
if [ $GENUINE != $REPLACE ]; then
	sed -E -i "s/\b$GENUINE\b/$REPLACE/" */*.php
fi

# On supprime les traductions en double dans les fichiers linguistiques
sed -E -i "/\b$DROP_LIST\b/d" */*.php

FILES_LIST="../admin/*.php ../lib/* ../../*.php ../../update/*.php"

# On liste les fichiers qui vont être modifiés
HR="----------------------"
echo $HR
grep -nE "\b$DROP_LIST\b" $FILES_LIST | sed -E 's/\s+/ /g'
echo $HR

# On remplace les clés supprimées par la nouvelle clé
sed -E -i "s/\b$DROP_LIST\b/$REPLACE/g" $FILES_LIST

# on commit
git commit -am "$REPLACE remplace $DROP_LIST pour les traductions"

# sed -Ei 's/php echo L_STATICS_ACTIVE\b/= L_ACTIVE/' admin/statiques.php
# git diff
# vim admin/categories.php
# git diff
# history
# grep L_ACTIVE fr/*.php
# grep L_ACTIVE lang/fr/*.php
# grep L_ACTIVE lang/*/*.php
# grep L_(CAT_LIST|STATICS)_ACTIVE lang/*/*.php
# grep -E 'L_(CAT_LIST|STATICS)_ACTIVE\b' lang/*/*.php
# sed -E -i 'L_STATICS_ACTIVE\b/d' lang/*/*.php
# sed -E -i '/L_STATICS_ACTIVE\b/d' lang/*/*.php
# grep -E 'L_(CAT_LIST|STATICS)_ACTIVE\b' lang/*/*.php
# sed -E -i 's/L_CAT_LIST_ACTIVE\b/L_ACTIVE/' lang/*/*.php
# git diff
# git commit -am 'L_ACTIVE remplace L_(CAT_LIST|STATICS)_ACTIVE pour traductions'
# git status
# sed -E -i '/\bL_PROFIL_MAIL\b/d' lang/*/*.php
# sed -E -i 's/\bL_PROFIL_MAIL\b/L_USER_MAIL/' admin/*.php lib/* ../*.php
# git diff
# git commit -am 'L_PROFIL_MAIL remplacé par L_USER_MAIL'
