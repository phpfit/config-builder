<?php

namespace PhpFit\ConfigBuilder;

use Composer\Composer;
use Composer\InstalledVersions;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Script\Event;
use PhpFit\SourceGenerator\Generator;

class ConfigBuilderPlugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => 'onAutoloadDump'
        ];
    }

    public static function onAutoloadDump(Event $event)
    {
        $composer = $event->getComposer();
        $config = $composer->getConfig();
        $vendor_dir = $config->get('vendor-dir');
        $composer_dir = $vendor_dir . '/composer/';
        $home_path = dirname($vendor_dir);
        $installed_file = $composer_dir . 'installed.json';
        if (!is_file($installed_file)) {
            return;
        }

        $installed = file_get_contents($installed_file);
        $installed = json_decode($installed);
        $packages = $installed->packages;

        $app_config_dir = $home_path . '/etc/config';

        $nl = PHP_EOL;
        foreach ($packages as $package) {
            if (!isset($package->extra)) {
                continue;
            }
            $extra = $package->extra;
            if (!isset($extra->phpfit)) {
                continue;
            }
            $phpfit = $extra->phpfit;
            if (!isset($phpfit->config)) {
                continue;
            }

            $config_file = realpath($composer_dir . $package->{'install-path'});
            $config_file = chop($config_file, '/') . '/' . $phpfit->config;

            $configs = include $config_file;
            foreach ($configs as $name => $config) {
                $config_file = $app_config_dir . '/' . $name . '.php';
                $file_config = [];
                if (is_file($config_file)) {
                    $file_config = include $config_file;
                }

                $file_config = array_replace_recursive($file_config, $config);

                $tx = '<?php' . $nl . $nl;
                $tx .= 'return ' . Generator::array($file_config) . ';';

                file_put_contents($config_file, $tx);
            }
        }
    }
}
