<?php
date_default_timezone_set("Europe/Rome");

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);

if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/src/config/config.yml'));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/src/views',
    'twig.class_path' => __DIR__.'/vendor/Twig/lib',
    'twig.options' => array('cache' => __DIR__.'/cache'),
));

// Svg Importer Extension
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
  $twig->addExtension(new AppBundle\Twig\Extension\SvgImporterExtension());
  return $twig;
}));

$app['debug'] = true;
$app['current_url'] = $_SERVER['REQUEST_URI'];
$app['project'] = "ITCSS";
$app['build_dir'] = 'build/';
$app['env'] = getenv('APP_ENV') ? getenv('APP_ENV') : "dev";
$app['base_url'] = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'];
$app['base_url'] = str_replace('index.php', '', $app['base_url']);
$app['asset_url'] = $app['base_url'] . 'build/';

$menu['blocks'] = getMenuFromFilesystem();
$menu['core'] = getMenuFromFilesystem("src/views/core/", "core");
$menu['pages'] = getMenuFromFilesystem("src/views/pages/", "page");
$standalone = false;

//Entrypoint Styleguide
$app->get('/styleguide', function() use($app, $menu) {
  return $app['twig']->render(
    'styleguide/index.html.twig',
    array(
      'app' => $app,
      'menu' => $menu,
    )
  );
});

$app->get('/styleguide/{file}', function($file) use($app, $menu) {
  return $app['twig']->render(
    'styleguide/' . $file . '.html.twig',
    array(
      'app' => $app,
      'menu' => $menu,
      'standalone' => false,
    )
  );
});

// Carica un blocco standalone
$app->get('/block/{block}', function($block) use($app) {
  $base = blockname_to_filesystem($block);
  $modificators = getModificators($base);
  $with_modificators = (count($modificators) > 1);
  $contents = getContents($base);
  $with_contents = (count($contents) > 1);
  $files = array_unique(array_merge(array($base), $modificators, $contents));
  $arrrrr = array_slice(explode('-', $block), -1, 1);
  $name = $arrrrr[0];

  return $app['twig']->render(
    'styleguide/block.html.twig',
    array(
      'app' => $app,
      'block' => $block,
      'block_filename' => $files,
      'block_name' => $name,
      'testing' => isset($_GET['testing']) ? $_GET['testing'] : false,
      'with_js' => file_exists('js/blocks/' . $name . '.js'),
      'with_modificators' => $with_modificators,
      'with_contents' => $with_contents,
    )
  );
});

// Carica un core standalone
$app->get('/core/{block}', function($block) use($app) {
  $file = blockname_to_filesystem($block);
  return $app['twig']->render(
    'styleguide/core.html.twig',
    array(
      'app' => $app,
      'block' => $block,
      'block_filename' => $file . '.html.twig',
    )
  );
});

// Carica una pagina in pages
$app->get('/page/{page}', function($page) use($app) {
  $file = blockname_to_filesystem($page);

  return $app['twig']->render(
    'pages/' . $file . '.html.twig',
    array(
      'app' => $app,
      'title' => $page,
      'standalone' => false,
    )
  );
})->bind('page');

// Carica una pagina nella root di views
$app->get('/{page}', function($page) use($app) {
  return $app['twig']->render(
    'pages/' . $page . '.html.twig',
    array(
      'app' => $app,
      'title' => $page,
      'standalone' => false,
    )
  );
})
->value('page', 'home');

$app->run();

function blockname_to_filesystem($block){
  return str_replace("-", '/', $block);
}

function getMenuFromFilesystem($folder = "src/views/blocks/", $url_prefix = "block", $base = ""){
  if($base == ""){
    $base = $folder;
  }
  $blocks = array();
  if ($entries = scandir($folder, 0)) {
    foreach($entries as $entry){
      if ($entry == "."  or $entry == ".." or $entry == ".DS_Store") {
        continue;
      }
      if(strpos($entry, "--") !== false){
        continue;
      }
      if(strpos($entry, "..") !== false){
        continue;
      }
      if(is_file($folder . $entry)){
        $url = $url_prefix . '/' . str_replace("/", '-', str_replace($base, "", $folder)) . str_replace(".html.twig", "", $entry);
        $blocks[] = array(
          'name' => str_replace(".html.twig", "", $entry),
          'url' => $url,
        );
      }
      if(is_dir($folder . $entry)){
        $blocks[] = array(
          'name' => $entry,
          'children' => getMenuFromFilesystem($folder.$entry.'/', $url_prefix,  $base)
        );
      }
    }
  }
  return $blocks;
}

function getModificators($file){
  return getFiles($file, '--');
}

function getContents($file){
  return getFiles($file, '..');
}

function getFiles($file, $separator = '--'){
  $files = array($file);
  $arr = explode("/", $file);
  $name = $arr[count($arr)-1];
  unset($arr[count($arr)-1]);
  $folder = implode("/", $arr);

  if ($handle = opendir('src/views/blocks/' . $folder)) {
    while (false !== ($entry = readdir($handle))) {
      if ($entry == "." or $entry == "..") {
        continue;
      }
      if((strpos($entry, $separator) > 0) and (strpos($entry, $name) === 0)){
        $new = $folder .'/'. str_replace(".html.twig", "", $entry);
        $files[] = $new;
      }
    }
    closedir($handle);
  }

  return $files;
}
