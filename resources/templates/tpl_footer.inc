<?php
/**
 * This file is part of gamify project.
 * Copyright (C) 2014  Paco Orozco <paco_@_pacoorozco.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * @category   Pakus
 * @package    Footer
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>
    </div><!--/.container -->
        <div id="push"></div>
    </div><!--/wrap -->

      <footer id="footer">
          <div class="container">
              <p class="text-muted credit">Powered by <a href="https://github.com/pacoorozco/gamify" title="gamify on GitHub">gamify v<?= APP_VERSION; ?></a> copyright &copy; <?= date("Y"); ?> by Paco Orozco - Released as free software under <a rel="license" href="http://www.gnu.org/licenses/gpl-2.0.html">GPL v2</a>.</p>
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
