<?php

use Symfony\Component\Yaml\Yaml;

class Permissions extends Seeder
{
    public function init()
    {
        $faker = $this->faker;

        $this->define('permissions', function ($data) use ($faker) {

            $value = ['allow', 'deny'];

            return [
                'role_id' => $data->role_id,
                'node'    => $data->node,
                'item'    => $data->item,
                'value'   => $value[array_rand($value)],
            ];

        }, $truncate = true);
    }

    public function run()
    {
        $output = $this->output;
        $file   = getcwd().'/resources/yaml/permissions.yml';
        $yaml   = Yaml::parse(file_get_contents($file));

        $this->seed(function ($counter) use ($yaml, $output) {

            if ($counter > 5) {
                $output->writeln('');
                $output->writeln('<comment>Maximum of 5 records. The value depends on the total number of roles.</comment>');
                die();
            }

            foreach ($yaml as $node => $values) {
                foreach ($values as $item) {
                    $data = [
                        'role_id' => $counter,
                        'node'    => $node,
                        'item'    => $item,
                    ];
                    $this->factory('permissions')->create($data);
                }
            }
        });

        return;
    }
}
