<!DOCTYPE html>
<!--
  Google HTML5 slide template

  Authors: Luke Mahé (code)
           Marcin Wichary (code and design)
           
           Dominic Mazzoni (browser compatibility)
           Charles Chen (ChromeVox support)

  URL: http://code.google.com/p/html5slides/
-->
<html>
    <head>
        <title>Presentation</title>

        <meta charset='utf-8'>
        <script src='http://html5slides.googlecode.com/svn/trunk/slides.js'></script>
        <style>
            .slides.template-bostonwp > article:not(.nobackground):not(.biglogo) {
                background: url(<?php echo CPTSLIDES_URL; ?>blackbar.png) 0 600px repeat-x,
                    url(<?php echo CPTSLIDES_URL; ?>bostonwp.png) 810px 614px no-repeat;  
                background-color: white;  
            }
            .slides.template-bostonwp > article .source {
                font-size: 15px;
                letter-spacing: 0;
                line-height: 0px;
                top: 625px;
            }
            .slides.template-bostonwp > article pre{
                font-size: 16px;
                line-height: 24px;
                letter-spacing: 0;
            }
        </style>
    </head>
    <body style='display: none'>
        <section class='slides template-bostonwp'>
            <article>
                <h1><?php echo single_term_title(); ?></h1>
                <p>
                    Jon Bishop
                    <br>
                    June 25, 2012
                </p>
                <div class="source">
                    http://www.jonbishop.com/cpt.zip
                </div>
            </article>
            <?php
            global $query_string;
            $args = array_merge($wp_query->query, array(
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'posts_per_page' => -1
                    ));
            query_posts($args);
            ?>
            <?php if (have_posts())
                while (have_posts()) : the_post(); ?>
                    <article>
                        <h3>
                            <?php the_title(); ?>
                        </h3>

                        <?php the_content(); ?>
                    </article>
                <?php endwhile; // end of the loop. ?>
        </section>
    </body>
</html>
