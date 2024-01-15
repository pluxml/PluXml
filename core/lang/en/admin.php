<?php

const L_ID = '#';
const L_MENU = 'Menu';
const L_UNKNOWN_ERROR = 'Unknown error';
const L_CONFIRM_DELETE = 'Confirm the deletion?';
const L_SAVE_FILE = 'Save the file';
const L_SAVE_FILE_SUCCESSFULLY = 'File saved successfully';
const L_SAVE_FILE_ERROR = 'Error while saving file';
const L_FILE_REQUIRED = 'Please select a file';
const L_REPLY = 'Reply';
const L_REPLY_TO = 'Reply to';
const L_CANCEL = 'Cancel';
const L_DELETE = 'Delete';
const L_DELETE_FILE = 'Delete file';
const L_DELETE_FOLDER = 'Delete folder';
const L_DELETE_SUCCESSFUL = 'Successfully deleted';
const L_DELETE_FILE_ERR = 'Error while deleting file';
const L_RENAME_FILE_SUCCESSFUL = 'Successfully renamed file';
const L_RENAME_FILE_ERR = 'Error while processing file';
const L_RENAME_FILE = 'Rename file';
const L_THUMBNAIL = 'Thumbnail (optional)';
const L_THUMBNAIL_SELECTION = 'Select image';
const L_THUMBNAIL_TITLE = 'Image Title (optional)';
const L_THUMBNAIL_ALT = 'Alternative text of the image (optional)';

const L_ERR_INVALID_DATE_CREATION = 'Invalid creation date';
const L_ERR_INVALID_DATE_UPDATE = 'Invalid date updated';
const L_INVALID_VALUE = 'Invalid value';

// class.plx.admin.php

const L_SAVE_ERR = 'Error while saving data';
const L_NO_ENTRY = 'No entry';
const L_ERR_USER_EMPTY = 'Please enter a username';
const L_ERR_PASSWORD_EMPTY = 'Please enter a password';
const L_ERR_PASSWORD_EMPTY_CONFIRMATION = 'Wrong or missing password';
const L_ERR_INVALID_EMAIL = 'Invalid email adress';
const L_ERR_INVALID_SITE = 'Wrong site url';
const L_ERR_INVALID_ARTICLE_IDENT = 'Wrong article id !';
const L_DEFAULT_NEW_CATEGORY_URL = 'new-category';
const L_DEFAULT_NEW_STATIC_URL = 'new-page';
const L_DEFAULT_NEW_ARTICLE_URL = 'new-article';
const L_ARTICLE_SAVE_SUCCESSFUL = 'Post was succesfully created';
const L_ARTICLE_MODIFY_SUCCESSFUL = 'Post was succesfully updated';
const L_ARTICLE_DELETE_SUCCESSFUL = 'Post was succesfully deleted';
const L_ARTICLE_SAVE_ERR = 'Post couldn\'t be saved';
const L_ARTICLE_DELETE_ERR = 'An error occured : post couldn\'t be deleted';
const L_ERR_UNKNOWN_COMMENT = 'Selected comment no longer exists';
const L_ERR_URL_ALREADY_EXISTS = 'Url already in use. Please change the value of the field \'Url\'';

// class.plx.media.php

const L_PLXMEDIAS_MEDIAS_FOLDER_ERR = 'Coul\'t create the media folder for connected user';
const L_PLXMEDIAS_ROOT = 'root';
const L_PLXMEDIAS_DELETE_FILES_SUCCESSFUL = 'Successfully removed files';
const L_PLXMEDIAS_DELETE_FILES_ERR = 'Error while deleting a file';
const L_PLXMEDIAS_DELETE_FILE_SUCCESSFUL = 'File successfully deleted';
const L_PLXMEDIAS_DELETE_FILE_ERR = 'Error while deleting the file';
const L_PLXMEDIAS_DEL_FOLDER_ERR = 'Error during the folder deletion';
const L_PLXMEDIAS_DEL_FOLDER_SUCCESSFUL = 'Folder sucessfully deleted';
const L_PLXMEDIAS_NEW_FOLDER_ERR = 'Could\'t create folder';
const L_PLXMEDIAS_NEW_FOLDER_SUCCESSFUL = 'Folder succesfully created';
const L_PLXMEDIAS_NEW_FOLDER_EXISTS = 'This folder already exists';
const L_PLXMEDIAS_WRONG_FILESIZE = 'A file\'s size is bigger than';
const L_PLXMEDIAS_WRONG_FILEFORMAT = 'The file type is not allowed';
const L_PLXMEDIAS_UPLOAD_ERR = 'Error while sending a file';
const L_PLXMEDIAS_UPLOAD_SUCCESSFUL = 'File sent successfully';
const L_PLXMEDIAS_UPLOADS_ERR = 'Error while uploading files';
const L_PLXMEDIAS_UPLOADS_SUCCESSFUL = 'Files sent successfully';
const L_PLXMEDIAS_MOVE_FILES_SUCCESSFUL = 'Files sucessfully moved';
const L_PLXMEDIAS_MOVE_FILE_SUCCESSFUL = 'File sucessfully moved';
const L_PLXMEDIAS_MOVE_FILES_ERR = 'An error occured while moving the files';
const L_PLXMEDIAS_MOVE_FILE_ERR = 'An error occured while moving the file';
const L_PLXMEDIAS_MOVE_FOLDER = 'Move';
const L_PLXMEDIAS_RECREATE_THUMB_ERR = 'Error while creating the thumbnail';
const L_PLXMEDIAS_RECREATE_THUMBS_ERR = 'Error creating thumbnail';
const L_PLXMEDIAS_RECREATE_THUMB_SUCCESSFUL = 'Thumbnail successfully created';
const L_PLXMEDIAS_RECREATE_THUMBS_SUCCESSFUL = 'Thumbnails created successfully';

// article.php

const L_DEFAULT_NEW_ARTICLE_TITLE = 'New article';
const L_ERR_INVALID_PUBLISHING_DATE = 'Invalid publishing date.';
const L_ERR_UNKNOWN_ARTICLE = 'The article doesn\'t exist';
const L_ERR_FORBIDDEN_ARTICLE = 'You don\'t have permission to access this article!';
const L_BACK_TO_ARTICLES = 'Back to articles';
const L_ARTICLE_EDITING = 'Article editing';
const L_ARTICLE_TITLE = 'Title';
const L_ARTICLE_LIST_AUTHORS = 'Author';
const L_HEADLINE_FIELD = 'Headline (optional)';
const L_CONTENT_FIELD = 'Content';
const L_LINK_FIELD = 'Link to article';
const L_LINK_ACCESS = 'Go to article';
const L_LINK_VIEW = 'view';
const L_ARTICLE_STATUS = 'Status';
const L_DRAFT = 'Draft';
const L_PUBLISHED = 'Published';
const L_AWAITING = 'Awaiting validation';
const L_ARTICLE_DATE = 'Article date';
const L_NOW = 'now';
const L_ARTICLE_CATEGORIES = 'Categories';
const L_CATEGORY_HOME_PAGE = 'Homepage';
const L_ARTICLE_TAGS_FIELD = 'Tags';
const L_ARTICLE_TAGS_FIELD_TITLE = 'Separate tags with commas';
const L_ARTICLE_TOGGLER_TITLE = 'Tag list';
const L_NO_TAG = 'No tag';
const L_ALLOW_COMMENTS = 'Allow comments';
const L_ARTICLE_URL_FIELD = 'Url';
const L_ARTICLE_URL_FIELD_TITLE = 'URL field auto-fills when the article is created';
const L_ARTICLE_TEMPLATE_FIELD = 'Template';
const L_ARTICLE_MANAGE_COMMENTS = 'Manage comments';
const L_ARTICLE_MANAGE_COMMENTS_TITLE = 'Manage comments on this article';
const L_ARTICLE_NEW_COMMENT = 'Write a comment';
const L_ARTICLE_NEW_COMMENT_TITLE = 'Write a comment about this article';
const L_ARTICLE_DELETE_CONFIRM = 'Delete this article?';
const L_ARTICLE_PREVIEW_BUTTON = 'Preview';
const L_ARTICLE_DRAFT_BUTTON = 'Save draft';
const L_ARTICLE_PUBLISHING_BUTTON = 'Publish';
const L_ARTICLE_MODERATE_BUTTON = 'Submit for validation';
const L_ARTICLE_OFFLINE_BUTTON = 'Switch offline';
const L_ARTICLE_UPDATE_BUTTON = 'Save';
const L_CATEGORY_ADD_BUTTON = 'Add';
const L_ARTICLE_META_DESCRIPTION = '"Description" Meta tag content (optional)';
const L_ARTICLE_META_KEYWORDS = '"Keywords" Meta tag content (optional)';
const L_ARTICLE_TITLE_HTMLTAG = 'Title tag contents (optional)';
const L_ARTICLE_CHAPO_HIDE = 'hide';
const L_ARTICLE_CHAPO_DISPLAY = 'display';
const L_ARTICLE_ID = 'Ident';
const L_PINNED_ARTICLE = 'pinned article';

// auth.php

const L_AUTH_PAGE_TITLE = 'Authentication page';
const L_LOGOUT_SUCCESSFUL = 'Logout successful';
const L_LOGIN_PAGE = 'Login to administration';
const L_AUTH_LOST_FIELD = 'Login or email address';
const L_SUBMIT_BUTTON = 'Submit';
const L_ERR_WRONG_PASSWORD = 'Incorrect login or password';
const L_POWERED_BY = 'Powered by <a href="https://www.pluxml.org">PluXml</a>';
const L_ERR_MAXLOGIN = 'Too many failed login<br />Retry in % s minutes';
const L_LOST_PASSWORD_LOGIN = 'Log in';
const L_LOST_PASSWORD_SUCCESS = 'An email has been sent to the user';
const L_LOST_PASSWORD_ERROR = 'The link has expired';

//

const L_SORT_ASCENDING_DATE = 'ascending date';
const L_SORT_DESCENDING_DATE = 'descending date';
const L_SORT_ALPHABETICAL = 'alphabetical';
const L_SORT_REVERSE_ALPHABETICAL = 'reverse alphabetical';
const L_SORT_RANDOM = 'random';
const L_YES = 'Yes';
const L_NO = 'No';
const L_OK = 'Ok';
const L_NONE1 = 'none';
const L_NONE2 = 'none';

// categories.php

const L_CAT_TITLE = 'Category Manager';
const L_CAT_LIST_ID = 'ID';
const L_CAT_LIST_ACTIVE = 'Active';
const L_CAT_LIST_NAME = 'Category name';
const L_CAT_LIST_URL = 'Url';
const L_CAT_LIST_SORT = 'Article sorting';
const L_CAT_LIST_BYPAGE = 'Articles/page';
const L_CAT_LIST_ORDER = 'Rank';
const L_CAT_LIST_MENU = 'Menu';
const L_DISPLAY = 'Display';
const L_HIDE = 'Hide';
const L_OPTIONS = 'Options';
const L_NEW_CATEGORY = 'New category';
const L_FOR_SELECTION = 'Selected items...';
const L_CAT_APPLY_BUTTON = 'Change categories list';
const L_CAT_UNKNOWN = 'Unknown category';
const L_ERR_CATEGORY_ALREADY_EXISTS = 'Category name already used';

// categorie.php

const L_EDITCAT_PAGE_TITLE = 'Edit category options';
const L_EDITCAT_DESCRIPTION = 'Description';
const L_EDITCAT_DISPLAY_HOMEPAGE = 'Show articles on the homepage';
const L_EDITCAT_TEMPLATE = 'Template';
const L_EDITCAT_BACK_TO_PAGE = 'Back to categories';
const L_EDITCAT_UPDATE = 'Update this category';
const L_EDITCAT_TITLE_HTMLTAG = 'Title tag contents (optional)';
const L_EDITCAT_META_DESCRIPTION = '"Description" Meta tag content	(optional)';
const L_EDITCAT_META_KEYWORDS = '"Keywords" Meta tag content	(optional)';

// commentaire.php

const L_COMMENT_ORPHAN = 'no article';
const L_COMMENT_ORPHAN_STATUS = 'not displayed (we advise you to delete this comment)';
const L_COMMENT_ARTICLE_LINKED = 'Article';
const L_COMMENT_ARTICLE_LINKED_TITLE = 'Article linked to this comment';
const L_COMMENT_OFFLINE = 'offline';
const L_COMMENT_ONLINE = 'online';
const L_COMMENT_ONLINE_TITLE = 'Published comments';
const L_BACK_TO_ARTICLE_COMMENTS = 'Back to this article\'s comments';
const L_BACK_TO_COMMENTS = 'Back to comments';
const L_COMMENT_EDITING = 'Comment edit';
const L_COMMENT_AUTHOR_FIELD = 'Author';
const L_COMMENT_TYPE_FIELD = 'Comment type';
const L_COMMENT_DATE_FIELD = 'Date and time of publication';
const L_COMMENT_IP_FIELD = 'IP';
const L_COMMENT_SITE_FIELD = 'Site';
const L_COMMENT_EMAIL_FIELD = 'E-mail';
const L_COMMENT_STATUS_FIELD = 'Status';
const L_COMMENT_LINKED_ARTICLE_FIELD = 'Linked article';
const L_COMMENT_ARTICLE_FIELD = 'Comments';
const L_COMMENT_DELETE_CONFIRM = 'Delete this comment?';
const L_COMMENT_PUBLISH_BUTTON = 'Confirm publication';
const L_COMMENT_OFFLINE_BUTTON = 'Switch offline';
const L_COMMENT_ANSWER_BUTTON = 'Reply to this comment';
const L_COMMENT_UPDATE_BUTTON = 'Update';
const L_COMMENT_WRITTEN_BY = 'Written by';
const L_COMMENT_SAVE_SUCCESSFUL = 'Comment was succesfully saved';
const L_COMMENT_UPDATE_ERR = 'Error updating comment';
const L_COMMENT_DELETE_SUCCESSFUL = 'Comment was sucessfully deleted';
const L_COMMENT_DELETE_ERR = 'An error occured in deletion of the comment';
const L_COMMENT_VALIDATE_SUCCESSFUL = 'Comment was sucessfully validated';
const L_COMMENT_VALIDATE_ERR = 'An error occured in the validation';
const L_COMMENT_MODERATE_SUCCESSFUL = 'Sucessfull moderation';
const L_COMMENT_MODERATE_ERR = 'An error occured in the moderation';

// sous_navigation/commentaires.php

const L_COMMENT_NEW_COMMENT_TITLE = 'Write a new comment for this article';
const L_COMMENT_NEW_COMMENT = 'Write a new comment';

// commentaire_new.php

const L_ERR_ANSWER_UNKNOWN_COMMENT = 'The comment you are trying to reply to no longer exists!';
const L_ERR_ANSWER_OFFLINE_COMMENT = 'Comment is offline, you can\'t answer it!';
const L_ERR_COMMENT_UNKNOWN_ARTICLE = 'The article doesn\'t exist and you can\'t comment it!';
const L_ERR_CREATING_COMMENT = 'An error occured while creating the comment';
const L_CREATING_COMMENT_SUCCESSFUL = 'Comment succesfully created';
const L_CREATE_NEW_COMMENT = 'Write a comment';
const L_COMMENT_SAVE_BUTTON = 'Save';
const L_ARTICLE_COMMENTS_LIST = 'Comments for this article (from most recent to the oldest)';
const L_COMMENT_ANSWER_TITLE = 'Reply to this comment';
const L_COMMENT_ANSWER = 'Answer';

// comments.php

const L_COMMENTS_ARTICLE_SCOPE = 'Article';
const L_COMMENTS_GLOBAL_SCOPE = 'entire site';
const L_COMMENTS_LIST_DATE = 'Date';
const L_COMMENTS_LIST_AUTHOR = 'Author';
const L_COMMENTS_LIST_MESSAGE = 'Message';
const L_COMMENTS_LIST_ACTION = 'Action';
const L_COMMENT_EDIT = 'Edit';
const L_COMMENT_EDIT_TITLE = 'Edit this comment';
const L_COMMENT_DELETE = 'Delete';
const L_COMMENT_OFFLINE_FEEDS_TITLE = 'Rss feed for offline comments';
const L_COMMENT_OFFLINE_FEEDS = 'Offline comments';
const L_COMMENT_ONLINE_FEEDS_TITLE = 'Rss feed for online comments';
const L_COMMENT_ONLINE_FEEDS = 'Online comments';
const L_COMMENTS_PRIVATE_FEEDS = 'Private feeds';
const L_COMMENTS_ONLINE_LIST = 'List of published reviews';
const L_COMMENTS_OFFLINE_LIST = 'Comments awaiting moderation';
const L_COMMENTS_ALL_LIST = 'Comments List';
const L_COMMENT_SET_ONLINE = 'Set online';
const L_COMMENT_SET_OFFLINE = 'Set Offline';

// index.php

const L_SEARCH = 'Search';
const L_SEARCH_PLACEHOLDER = 'article id or title';
const L_ARTICLES_ALL_CATEGORIES = 'All Categories ...';
const L_ARTICLES_ALL_AUTHORS	= 'All authors ...';
const L_ALL = 'All';
const L_ALL_PUBLISHED = 'Published';
const L_ALL_DRAFTS = 'Drafts';
const L_ALL_AWAITING_MODERATION = 'Awaiting validation';
const L_ARTICLES_FILTER_BUTTON = 'Filter';
const L_CATEGORIES_TABLE = 'Categories';
const L_SPECIFIC_CATEGORIES_TABLE = 'Spectific categories';
const L_ALL_ARTICLES_CATEGORIES_TABLE = 'All articles';
const L_ARTICLES_LIST = 'Article list';
const L_ARTICLE_LIST_DATE = 'Date';
const L_ARTICLE_LIST_TITLE = 'Title';
const L_ARTICLE_LIST_CATEGORIES = 'Category';
const L_ARTICLE_LIST_NBCOMS = 'N° coms';
const L_ARTICLE_LIST_AUTHOR = 'Author';
const L_ARTICLE_LIST_ACTION = 'Action';
const L_CATEGORY_HOME = 'Home';
const L_CATEGORY_DRAFT = 'Draft';
const L_ARTICLE_VIEW_TITLE = 'View this article online';
const L_ARTICLE_EDIT = 'Edit';
const L_ARTICLE_EDIT_TITLE = 'Edit this article';
const L_NEW_COMMENTS_TITLE = 'Comments awaiting moderation';
const L_VALIDATED_COMMENTS_TITLE = 'Published comments';
const L_TAGS_SAVE_SUCCESS = '%d tagged articles';
const L_TAGS_SAVE_ERROR = 'Saving tags in file tags.xml fails';

// medias.php

const L_MEDIAS_FILENAME = 'File name';
const L_MEDIAS_TITLE = 'Media manager';
const L_MEDIAS_DIRECTORY = 'Location';
const L_MEDIAS_BACK = 'Back';
const L_MEDIAS_MAX_UPLOAD_FILE = 'Maximum file size';
const L_MEDIAS_MAX_UPLOAD_NBFILE = 'Max number of files per upload';
const L_MEDIAS_MAX_POST_SIZE = 'Maximum data size';
const L_MEDIAS_SUBMIT_FILE = 'Send';
const L_MEDIAS_IMAGES = 'Pictures';
const L_MEDIAS_DOCUMENTS = 'Documents';
const L_MEDIAS_ADD_FILE = 'Add file';
const L_MEDIAS_DELETE_FOLDER = 'Delete folder';
const L_MEDIAS_DELETE_FOLDER_CONFIRM = 'Delete folder %s and its content ?';
const L_MEDIAS_FOLDER = 'File';
const L_MEDIAS_NEW_FOLDER = 'New Folder';
const L_MEDIAS_CREATE_FOLDER = 'Create folder';
const L_MEDIAS_FILESIZE = 'Size';
const L_MEDIAS_DATE = 'Date';
const L_MEDIAS_DIMENSIONS = 'Dimensions';
const L_MEDIAS_NO_FILE = 'No file';
const L_MEDIAS_RESIZE = 'Resize Images';
const L_MEDIAS_RESIZE_NO = 'Original Size';
const L_MEDIAS_THUMBS = 'Create thumbnails';
const L_MEDIAS_THUMBS_NONE = 'No thumbnail';
const L_MEDIAS_MODIFY = 'Edit';
const L_MEDIAS_THUMB = 'Thumbnail';
const L_MEDIAS_EXTENSION = 'Extension';
const L_MEDIAS_ADD = 'Add';
const L_MEDIAS_ALIGNMENT = 'Alignment';
const L_MEDIAS_ALIGN_NONE = 'None';
const L_MEDIAS_ALIGN_LEFT = 'Left';
const L_MEDIAS_ALIGN_CENTER = 'Center';
const L_MEDIAS_ALIGN_RIGHT = 'Right';
const L_MEDIAS_RECREATE_THUMB = 'Recreate thumbnail';
const L_MEDIAS_LINK_COPYCLP = 'Copy link to clipboard';
const L_MEDIAS_LINK_COPYCLP_ERR = 'Unable to copy link to clipboard';
const L_MEDIAS_LINK_COPYCLP_DONE = 'Copied link';
const L_MEDIAS_NEW_NAME = 'New name';
const L_MEDIAS_RENAME = 'Rename';

// parametres_affichage.php

const L_CONFIG_VIEW_FIELD = 'Display preferences';
const L_CONFIG_VIEW_SKIN_SELECT = 'Skin';
const L_CONFIG_VIEW_FILES_EDIT_TITLE = 'Edit theme files';
const L_CONFIG_VIEW_FILES_EDIT = 'Edit theme files';
const L_CONFIG_VIEW_SORT = 'Sorting articles';
const L_CONFIG_VIEW_BYPAGE = 'Articles per page';
const L_CONFIG_VIEW_BY_HOMEPAGE = 'Count of articles on the homepage';
const L_CONFIG_VIEW_BYPAGE_ARCHIVES = 'Articles per page in archives';
const L_CONFIG_VIEW_BYPAGE_TAGS = 'Articles per page in tags';
const L_CONFIG_VIEW_BYPAGE_ADMIN = 'Articles per page in administration';
const L_CONFIG_VIEW_SORT_COMS = 'Sorting comments';
const L_CONFIG_VIEW_BYPAGE_ADMIN_COMS = 'Comments per page in administraton';
const L_CONFIG_VIEW_IMAGES = 'Image Size (width x height)';
const L_CONFIG_VIEW_THUMBS = 'Thumbnails size (width x height)';
const L_CONFIG_VIEW_HOMESTATIC = 'Use a static page as Homepage';
const L_CONFIG_VIEW_HOMESTATIC_ACTIVE = 'Warning: this page is inactive';
const L_CONFIG_VIEW_PLUXML_RESSOURCES = 'Download themes at <a href="http://ressources.pluxml.org">ressources.pluxml.org</a>.';
const L_CONFIG_VIEW_BYPAGE_FEEDS = 'N° of Articles or comments in the Rss feed';
const L_CONFIG_VIEW_FEEDS_HEADLINE = 'Only display headlines in the Rss article feed';
const L_CONFIG_VIEW_FEEDS_HEADLINE_HELP = 'Headline field is empty, content is displayed instead';
const L_CONFIG_VIEW_FEEDS_FOOTER = 'Signature used in the end of every Rss feed\'s article';
const L_CONFIG_VIEW_UPDATE = 'Save display settings';
const L_CONFIG_VIEW_DISPLAY_EMPTY_CAT = 'Display categories without article';
const L_CONFIG_HOMETEMPLATE = 'Template of the homepage';

// parametres_avances.php

const L_CONFIG_ADVANCED_DESC = 'Advanced configuration';
const L_BUILD = 'Build';
const L_CONFIG_ADVANCED_URL_REWRITE = 'Enable url rewriting';
const L_CONFIG_ADVANCED_URL_REWRITE_ALERT = 'Warning: a .htaccess file already exists at your PluXml\'s installation root. Activating url rewriting will overwrite this file';
const L_CONFIG_ADVANCED_GZIP = 'Enable GZIP compression';
const L_CONFIG_ADVANCED_GZIP_HELP = 'Makes it possible to compress pages to save bandwidth, but could increase CPU usage';
const L_CONFIG_ADVANCED_CAPCHA = 'Enable anti spam-capcha';
const L_CONFIG_ADVANCED_LOSTPASSWORD = 'Enable password recovery';
const L_CONFIG_ADVANCED_ADMIN_KEY = 'Administration key (private URLs)';
const L_CONFIG_ADVANCED_KEY_HELP = 'Leave this field empty to rebuild key';
const L_CONFIG_ADVANCED_USERFOLDERS = 'Use separate medias folders for every writer';
const L_CONFIG_ADVANCED_USERSFOLDERS = 'Use separate medias folders from this profile';
const L_HELP_SLASH_END = 'Don\'t forget the slash at the end';
const L_CONFIG_ADVANCED_MEDIAS_FOLDER = 'Medias (folder) location';
const L_CONFIG_ADVANCED_ARTS_FOLDER = 'Articles (folder) location';
const L_CONFIG_ADVANCED_COMS_FOLDER = 'Comments (folder) location';
const L_CONFIG_ADVANCED_STATS_FOLDER = 'Static page (folder) location';
const L_CONFIG_ADVANCED_THEMES_FOLDER = 'Themes (folder) location';
const L_CONFIG_ADVANCED_PLUGINS_FOLDER = 'Plugins (folder) location';
const L_CONFIG_ADVANCED_CONFIG_FOLDER = 'Configuration files (folder) location';
const L_CONFIG_ADVANCED_UPDATE = 'Save advanced configuration';
const L_CONFIG_CUSTOM_CSSADMIN_PATH = 'Location and name of custom css file of the administration area (optional)';
const L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE = 'Email sending';
const L_CONFIG_ADVANCED_EMAIL_METHOD = 'Email sending method';
const L_CONFIG_ADVANCED_SMTP_TITLE = 'Sending emails with SMTP';
const L_CONFIG_ADVANCED_SMTP_SERVER = 'SMTP hostname';
const L_CONFIG_ADVANCED_SMTP_USERNAME = 'SMTP username';
const L_CONFIG_ADVANCED_SMTP_PASSWORD = 'SMTP password';
const L_CONFIG_ADVANCED_SMTP_PORT = 'SMTP port';
const L_CONFIG_ADVANCED_SMTP_SECURITY = 'SMTP encryption';
const L_CONFIG_ADVANCED_SMTPOAUTH_TITLE = 'Sending emails with SMTP and OAUTH2';
const L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL = 'Email address';
const L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID = 'Client ID';
const L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY = 'Client secret key';
const L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN = 'Token';
const L_CONFIG_ADVANCED_SMTPOAUTH_GETTOKEN = 'Generate a token';
const L_CONFIG_ADVANCED_EMAIL_SENDING_TITLE_HELP = 'Need help: <a href="https://wiki.pluxml.org/docs/customize/advancedconfig.html?highlight=smtp#envoi-d-e-mails">PluXml documentation</a>&nbsp;(fr)';
const L_CONFIG_ADVANCED_EMAIL_METHOD_HELP = 'No configuration is required for sendmail.';
const L_CONFIG_ADVANCED_SMTP_SERVER_HELP = 'SMTP server name (example: ssl0.ovh.net)';
const L_CONFIG_ADVANCED_SMTP_USERNAME_HELP = 'User name on the SMTP host (example: pluxml@monserveursmtp.com)';
const L_CONFIG_ADVANCED_SMTP_PASSWORD_HELP = 'User password on the SMTP host';
const L_CONFIG_ADVANCED_SMTP_PORT_HELP = 'SMTP host port number (default: 465)';
const L_CONFIG_ADVANCED_SMTPOAUTH_TITLE_HELP = 'PluXml allows to generate tokens only for the service <a href="https://cloud.google.com">GMAIL (Google)</a>.<br>Need help to generate the username and secret key: <a href="https://wiki.pluxml.org/docs/customize/advancedconfig.html?highlight=smtp#envoi-d-e-mails">PluXml documentation</a>&nbsp;(fr).';
const L_CONFIG_ADVANCED_SMTPOAUTH_EMAIL_HELP = 'Address used on the OAUTH2 service (example: pluxml@gmail.com)';
const L_CONFIG_ADVANCED_SMTPOAUTH_CLIENTID_HELP = 'Customer ID on the OAUTH2 service (example: 664335625964-uha1vop20qPluXml81ubjkkgfabbbj6d.apps.googleusercontent.com)';
const L_CONFIG_ADVANCED_SMTPOAUTH_SECRETKEY_HELP = 'The client key on the OAUTH2 service (example: PrsvKp6aprKpoP8snnCoC8-x)';
const L_CONFIG_ADVANCED_SMTPOAUTH_TOKEN_HELP = 'Save the customer ID and customer secret code so that you can generate the token.';

// parametres_base.php

const L_CONFIG_BASE_CONFIG_TITLE = 'Basic configuration';
const L_CONFIG_BASE_SITE_TITLE = 'Site title';
const L_CONFIG_BASE_SITE_SLOGAN = 'Subtitle / Site description';
const L_CONFIG_BASE_URL_HELP = 'Don\'t forget the slash at the end';
const L_CONFIG_BASE_DEFAULT_LANG = 'Default site language';
const L_CONFIG_BASE_TIMEZONE = 'Time Zone';
const L_CONFIG_BASE_ALLOW_COMMENTS = 'Allow comments';
const L_CONFIG_BASE_MODERATE_COMMENTS = 'Moderate comments when created';
const L_CONFIG_BASE_MODERATE_ARTICLES = 'Moderate articles for Editor and Publisher profiles';
const L_CONFIG_BASE_UPDATE = 'Save basic configuration';
const L_CONFIG_META_DESCRIPTION = 'Content of "description" meta tag (optional)';
const L_CONFIG_META_KEYWORDS = 'Content of "keywords" meta tag (optional)';
const L_CONFIG_BASE_ENABLE_RSS = 'Display RSS feeds';
const L_CONFIG_BASE_ENABLE_RSS_COMMENT = 'Display RSS feeds for comments';
const L_EVERY_BODY = 'Everybody';
const L_SUBSCRIBERS_ONLY = 'Subscribers only';

// parametres_edittpl.php

const L_CONFIG_EDITTPL_ERROR_NOTHEME = 'There is no such theme!';
const L_CONFIG_EDITTPL_TITLE = 'Theme edit';
const L_CONFIG_EDITTPL_SELECT_FILE = 'Choose file to edit:';
const L_CONFIG_EDITTPL_LOAD = 'Load';

// parametres_infos.php

const L_CONFIG_INFOS_TITLE = 'Information about PluXml';
const L_CONFIG_INFOS_DESCRIPTION = 'Information about your PluXml installation, can be useful to repair it if needed.';
const L_CONFIG_INFOS_NB_CATS = 'N° of categories :';
const L_CONFIG_INFOS_NB_STATICS = 'N° of static pages :';
const L_CONFIG_INFOS_WRITER = 'N° of users in session :';
const L_PLUXML_CHECK_VERSION = 'Checking version number on the official PluXml.org site';
const L_PLUXML_UPDATE_UNAVAILABLE = 'Can\'t check for updates as long as \'allow_url_fopen\' is disabled on this system';
const L_PLUXML_UPDATE_ERR = 'Update check failed for an unknown reason';
const L_PLUXML_UPTODATE = 'You are using PluXml\'s lastest version';
const L_PLUXML_UPDATE_AVAILABLE = 'A new PluXml version is available ! You can download it from';
const L_MAIL_TEST = 'Send a test email';
const L_MAIL_TEST_SUBJECT = 'Test email sent from %s';
const L_MAIL_TEST_SENT_TO = 'Test email sent to %s. Check your mailbox';
const L_MAIL_TEST_FAILURE = 'Issue for sending the test email';

// parametres_users.php

const L_CONFIG_USERS_TITLE = 'Manage users';
const L_CONFIG_USER = 'User';
const L_CONFIG_USERS_ID = 'User ID';
const L_CONFIG_USERS_ACTIVE = 'Active';
const L_CONFIG_USERS_ACTION = 'Action';
const L_CONFIG_USERS_NEW = 'New user';
const L_CONFIG_USERS_UPDATE = 'Modify the users\' list';
const L_ERR_LOGIN_ALREADY_EXISTS = 'Login ID already used';
const L_ERR_USERNAME_ALREADY_EXISTS = 'Username already in use';
const L_ERR_EMAIL_ALREADY_EXISTS = 'Email adress already in use';

// parametre_plugins.php

const L_BACK_TO_PLUGINS = 'Back to plugins\' page';
const L_NO_PLUGIN = 'No plugin';
const L_PLUGIN_NO_CONFIG = 'Not configured plugin';
const L_PLUGINS_CSS = 'Css code';
const L_PLUGINS_CSS_TITLE = 'Edit the css code of the plugin';
const L_CONTENT_FIELD_FRONTEND = 'Css file content site';
const L_CONTENT_FIELD_BACKEND = 'Css file content administrator';

// parametres_plugins.php

const L_PLUGINS_TITLE = 'Manage plugins';
const L_PLUGINS_VERSION = 'Version';
const L_PLUGINS_AUTHOR = 'Author';
const L_PLUGINS_ACTIVATE = 'Enable';
const L_PLUGINS_DEACTIVATE = 'Disable';
const L_PLUGINS_DELETE = 'Delete';
const L_PLUGINS_DELETE_ERROR = 'An error occured while deleting';
const L_PLUGINS_ENABLED_ERROR = '%s plugin enabled. Deletion forbidden';
const L_DELETE_FILE_ERROR = 'Failure for deleting %s file';
const L_PLUGINS_DELETE_SUCCESSFUL = 'Successfull deletion';
const L_PLUGINS_CONFIG = 'Configuration';
const L_PLUGINS_CONFIG_TITLE = 'Plugin\'s configuration';
const L_PLUGINS_HELP = 'Help';
const L_PLUGINS_HELP_TITLE = 'See how to use the plugin';
const L_PLUGINS_REQUIREMENTS = 'Requirements';
const L_PLUGINS_REQUIREMENTS_HELP = 'Available and active plugins to activate this one';
const L_PLUGINS_REQUIREMENTS_NONE = 'None';
const L_PLUGINS_ALPHA_SORT = 'Sort alphabetically plugins';
const L_PLUGINS_LOADING_SORT = 'Loading order';
const L_PLUGINS_ACTION = 'Action';
const L_PLUGINS_APPLY_BUTTON = 'Modify the plugins list';
const L_PLUGINS_ACTIVE_LIST = 'Active plugins';
const L_PLUGINS_INACTIVE_LIST = 'Inactive plugins';

// profil.php

const L_PROFIL_EDIT_TITLE = 'Profile edit';
const L_PROFIL = 'Profile';
const L_PROFIL_LOGIN = 'Login';
const L_PROFIL_USER = 'Username';
const L_PROFIL_MAIL = 'E-mail adress';
const L_PROFIL_ADMIN_LANG = 'Language for administration';
const L_PROFIL_INFOS = 'Information';
const L_PROFIL_UPDATE = 'Save profile';
const L_PROFIL_CHANGE_PASSWORD = 'Change password';
const L_PROFIL_PASSWORD = 'Password';
const L_PROFIL_CONFIRM_PASSWORD = 'Confirm password';
const L_PROFIL_UPDATE_PASSWORD = 'Change password';

// statique.php

const L_STATIC_BACK_TO_PAGE = 'Back to static page list';
const L_STATIC_UNKNOWN_PAGE = 'This static page doesn\'t exist!';
const L_STATIC_TITLE = 'Edit static page\'s source code';
const L_STATIC_VIEW_PAGE = 'View page';
const L_STATIC_ON_SITE = 'on site';
const L_STATIC_UPDATE = 'Save this page';
const L_STATIC_TITLE_HTMLTAG = 'Title tag contents (optional)';
const L_STATIC_META_DESCRIPTION = 'Meta tag "description" content for this static page (optional)';
const L_STATIC_META_KEYWORDS = 'Meta tag "keywords" content for this static page (optional)';

// statiques.php

const L_STATICS_PAGE_TITLE = 'Create and edit static pages';
const L_STATICS_ID = 'ID';
const L_STATICS_GROUP = 'Group';
const L_STATICS_TITLE = 'Title';
const L_STATICS_URL = 'Url';
const L_STATICS_ACTIVE = 'Active';
const L_STATICS_ORDER = 'Rank';
const L_STATICS_MENU = 'Menu';
const L_STATICS_ACTION = 'Action';
const L_STATICS_TEMPLATE_FIELD = 'Template';
const L_STATICS_PAGE_HOME = 'Set as Homepage';
const L_STATICS_HOME_PAGE = 'Homepage';
const L_VIEW = 'See';
const L_STATICS_SRC_TITLE = 'Edit source code for this page';
const L_STATICS_SRC = 'Edit';
const L_STATICS_NEW_PAGE = 'New page';
const L_STATICS_UPDATE = 'Modify static page list';
const L_ERR_STATIC_ALREADY_EXISTS = 'Title already used';

// top.php

const L_PROFIL_ADMIN = 'Administrator';
const L_PROFIL_MANAGER = 'Manager';
const L_PROFIL_MODERATOR = 'Moderator';
const L_PROFIL_EDITOR = 'Editor';
const L_PROFIL_WRITER = 'Writer';
const L_PROFIL_SUBSCRIBER = 'Subscriber';
const L_ADMIN = 'Administration';
const L_LOGIN = 'Connected as';
const L_ADMIN_LOGOUT = 'Disconnect';
const L_ADMIN_LOGOUT_TITLE = 'Leave administrator\'s session';
const L_BACK_TO_SITE = 'Home';
const L_BACK_TO_SITE_TITLE = 'Back to homepage';
const L_BACK_TO_BLOG = 'Blog';
const L_BACK_TO_BLOG_TITLE = 'Back to blog';
const L_MENU_ARTICLES = 'Articles';
const L_MENU_ARTICLES_TITLE = 'List or Edit Articles';
const L_MENU_NEW_ARTICLES = 'New article';
const L_MENU_NEW_ARTICLES_TITLE = 'New article';
const L_MENU_STATICS_TITLE = 'List or Edit Static pages';
const L_MENU_STATICS = 'Static pages';
const L_MENU_COMMENTS_TITLE = 'List or Edit Comments';
const L_MENU_COMMENTS = 'Comments';
const L_MENU_MEDIAS_TITLE = 'Upload and insert media';
const L_MENU_MEDIAS = 'Media';
const L_MENU_CATEGORIES_TITLE = 'Create, manage, edit categories';
const L_MENU_CATEGORIES = 'Categories';
const L_MENU_CONFIG_TITLE = 'Configure PluXml';
const L_MENU_CONFIG = 'Parameters';
const L_MENU_PROFIL_TITLE = 'Manage your user profiles';
const L_MENU_PROFIL = 'Profile';
const L_WARNING_INSTALLATION_FILE = 'install.php file can still be found at your PluXml root.<br />For security reasons, it is strongly recommended to <a class="lnkdelete" href="%s">delete</a> it.';

// user.php

const L_USER_UNKNOWN = 'Unknown user';
const L_USER_LANG = 'Language used in administration';
const L_USER_MAIL = 'E-mail adress';
const L_USER_INFOS = 'Information';
const L_USER_UPDATE = 'Update this user';
const L_USER_PAGE_TITLE = 'Edit user options';
const L_USER_BACK_TO_PAGE = 'Back to users\' list';

//

const L_MENU_CONFIG_BASE_TITLE = 'Edit basic PluXml configuration';
const L_MENU_CONFIG_BASE = 'Basic configuration';
const L_MENU_CONFIG_VIEW_TITLE = 'Change your PluXml\'s display settings';
const L_MENU_CONFIG_VIEW = 'Display settings';
const L_MENU_CONFIG_USERS_TITLE = 'Manage user accounts on your PluXml';
const L_MENU_CONFIG_USERS = 'User accounts';
const L_MENU_CONFIG_ADVANCED_TITLE = 'Edit advanced configuration on your PluXml';
const L_MENU_CONFIG_ADVANCED = 'Advanced configuration';
const L_MENU_CONFIG_INFOS_TITLE = 'Information about your PluXml';
const L_MENU_CONFIG_INFOS = 'Information';
const L_MENU_CONFIG_PLUGINS_TITLE = 'Manage plugins';
const L_MENU_CONFIG_PLUGINS = 'Plugins';
const L_THEMES = 'Themes';
const L_THEMES_TITLE = 'Manage themes';
const L_HELP = 'Help';
const L_HELP_TITLE = 'See help';
const L_BACK_TO_THEMES = 'Back to themes';
const L_CONFIG_THEME_UPDATE = 'Change Theme';

