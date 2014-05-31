<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: footer.inc.php 65 2014-04-21 18:09:54Z paco $
 *
 */

/* Check if this is a valid include */
defined('IN_SCRIPT') or die('Invalid attempt');

?>
    </div><!--/.container -->
        <div id="push"></div>
    </div><!--/wrap -->

      <footer id="footer">
          <div class="container">
              <p class="text-muted credit">GoW! v<?php echo $CONFIG['version']; ?> - Powered by <a href="https://github.com/pacoorozco/gamify" title="Gamify Project">gamify</a> - <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.ca">Llicència Creative Commons Reconeixement-CompartirIgual</a></p>
          </div>
      </footer>

    <!-- Placed at the end of the document so the pages load faster -->
    <script>
        head.js(
                "//code.jquery.com/jquery-1.11.0.min.js",
                "//code.jquery.com/jquery-migrate-1.2.1.min.js",
                "//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js",
                "//tinymce.cachefly.net/4.0/tinymce.min.js",
                "//cdn.datatables.net/1.10.0/js/jquery.dataTables.js",
                "//cdn.datatables.net/plug-ins/28e7751dbec/integration/bootstrap/3/dataTables.bootstrap.js",
                "js/jquery.liveSearch.js",
                "js/chosen/chosen.jquery.min.js",
                "js/jQuery-custom-input-file.js",
                "js/jquery.upload.js",
                "js/gamify.js",
                function () {
                    gamify_helper.init();
                }
                );
    </script>

    <!-- Google Analytics -->
    <script>
  (function (i,s,o,g,r,a,m) {i['GoogleAnalyticsObject']=r;i[r]=i[r]||function () {
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-45534443-3', 'upcnet.es');
  ga('send', 'pageview');
    </script>

  </body>
</html>
