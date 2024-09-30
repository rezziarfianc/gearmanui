<?php

/*
 * This file is part of the GearmanUI package.
 *
 * (c) Rodolfo Ripado <ggaspaio@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GearmanUI;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Symfony\Component\Yaml\Yaml;


class ConfigurationProvider implements ServiceProviderInterface {

    const CONFIG_FILE = '/../../config.yml';

    public function register(Application $app) {

        if (!is_file(__DIR__ . static::CONFIG_FILE)) {
            throw new \Exception(
                sprintf('The GearmanUI config file \'%1$s\' doesn\'t seem to exist. Copy the default \'%1$s.dist\' and rename it to \'%1$s\'.', static::CONFIG_FILE));
        }

        $config = Yaml::parse(__DIR__ . static::CONFIG_FILE);

        foreach ($config as $key => $param) {
            $app[$key] = $param;
        }

        $this->getServersFromEnv($app);

    }

    //this function loads the server from .env and replace the config
    private function getServersFromEnv(Application $app) {
        $serverKey =  'gearmanui.servers';
        $serverFromEnv = getenv('GEARMAN_SERVERS') ?? '';

        $processedServers = [];
        foreach (explode(',', $serverFromEnv) as $serverFromEnv) {
            $explodedServer = explode(':', $serverFromEnv);

            if (!isset($explodedServer[0]) || !isset($explodedServer[1]) || !isset($explodedServer[2])) {
                continue;
            }

            $processedServers[] = [
                'name' => $explodedServer[0],
                'addr' => "{$explodedServer[1]}:{$explodedServer[2]}",
            ]; 
        }

        if (!empty($processedServers)) {
            $app[$serverKey] =  $processedServers;
        }

    }


    public function boot(Application $app) {
    }
}
