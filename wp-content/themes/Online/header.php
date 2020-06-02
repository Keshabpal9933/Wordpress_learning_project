<!DOCTYPE html>
<html>
<head>
     
     <?php wp_head(); ?>
     
</head>
<body <?php body_class(); ?>>

<header class="sticky-top">

<div class="container">
     <nav class="site-nav">
          <?php
               $args = array(
                    'theme_location' => 'primary'
               );         
           ?>
          <?php wp_nav_menu($args); ?>
     </nav>
</div>

</header>