wp-side-comments
================

Based on Eric Anderson's [SideComments.js](https://github.com/aroc/side-comments) to enable inline commenting. Medium.com-style inline commenting for WordPress

Please note that this project is very much in beta. Alpha, even. It's being actively developed and there will be several items going upstream to the main javascript library, too.

I'd absolutely love to have some feedback in terms of bug reports or pull-requests!

Print
=====

Based on:
Plugin Name: WP-Print
Plugin URI: http://lesterchan.net/portfolio/programming/php/
Description: Displays a printable version of your WordPress blog's post/page.
Version: 2.50
Author: Lester 'GaMerZ' Chan
Author URI: http://lesterchan.net

Print Vars
==========

use this query vars on url:

wp_side_comments_print=1 -> Print post
wp_side_comments_printpage=1 -> Print with Page layout
wp_side_comments_print_csv=1 -> Export Csv with comment number per paragraph
wp_side_comments_print_csv=2 -> Export Csv with comment number per date
wp_side_comments_print_csv=3 -> Export Csv with comment number per author
number-options -> limit number of posts to print 
wp_side_comments_print_parent=1 -> Print post parent parents
