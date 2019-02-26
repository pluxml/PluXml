<nav class="grid profil-nav">
	<div class="col sml-6 text-left title">
    	<ul class="unstyled-list inline-list">
       		<li class="site-name">
       			<a href="<?php echo PLX_ROOT ?>/core/admin" title="<?php echo L_BACK_TO_SITE_TITLE ?>"><h1 class="h2 no-margin"><strong><?php echo plxUtils::strCheck($plxAdmin->aConf['title']) ?></strong></h1></a>
       		</li>
       		<li>
       			<small><a class="back-site" href="<?php echo PLX_ROOT ?>" title="<?php echo L_BACK_TO_SITE_TITLE ?>"><?php echo L_BACK_TO_SITE;?></a></small>
       		</li>
       		<?php if(isset($plxAdmin->aConf['homestatic']) AND !empty($plxAdmin->aConf['homestatic'])) : ?>
       		<li>
       			<small><a class="back-blog" href="<?php echo $plxAdmin->urlRewrite('?blog'); ?>" title="<?php echo L_BACK_TO_BLOG_TITLE ?>"><?php echo L_BACK_TO_BLOG;?></a></small>
       		</li>
       		<?php endif; ?>
       	</ul>
   	</div>
   	<div class="col sml-6 text-right profil">
   		<img class="img-circle" src="<?php echo PLX_CORE ?>admin/theme/images/pluxml.png">
       	<!-- <ul class="unstyled-list inline-list">
    		<li>
    			
    		</li>
    		<li>
    			<strong><?php echo plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?></strong>
    		</li>
    		<li>
    			<small><a class="logout" href="<?php echo PLX_CORE ?>admin/auth.php?d=1" title="<?php echo L_ADMIN_LOGOUT_TITLE ?>"><?php echo L_ADMIN_LOGOUT ?></a></small>
    		</li>
    	</ul> -->
	</div>
</nav>