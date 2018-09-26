<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ContainerApiGenerator
 *
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 */
class LayerContainerApiGenerator extends GeneratorCommand implements ComponentsGenerator
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:container:layer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Layer Container for apiato from scratch (API Part)';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $fileType = 'Container';

    /**
     * The structure of the file path.
     *
     * @var  string
     */
    protected $pathStructure = '{container-name}/*';

    /**
     * The structure of the file name.
     *
     * @var  string
     */
    protected $nameStructure = '{file-name}';

    /**
     * The name of the stub file.
     *
     * @var  string
     */
    protected $stubName = 'composer.stub';

    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['docversion', null, InputOption::VALUE_OPTIONAL, 'The version of all endpoints to be generated (1, 2, ...)'],
        ['doctype', null, InputOption::VALUE_OPTIONAL, 'The type of all endpoints to be generated (private, public)'],
        ['url', null, InputOption::VALUE_OPTIONAL, 'The base URI of all endpoints (/stores, /cars, ...)']
    ];

    /**
     * @return array
     */
    public function getUserInputs()
    {
        $ui = 'api';

        // containername as inputted and lower
        $containerName = $this->containerName;
        $_containerName = Str::lower($this->containerName);

        // name of the model (singular and plural)
        $model = $this->containerName;
        $models = Pluralizer::plural($model);

        // create the ContainerServiceProvider for the container
        $this->printInfoMessage('Generating Container Service');
        $this->call('apiato:generate:service', [
            '--container'   => $containerName,
            '--file'        => $containerName.'Service'
        ]);

        // create the ContainerServiceProvider for the container
        $this->printInfoMessage('Generating ContainerServiceProvider');
        $this->call('apiato:generate:serviceprovider', [
            '--container'   => $containerName,
            '--file'        => $containerName.'ServiceProvider',
            '--stub'        => 'containerserviceprovider',
        ]);

        // create the MainServiceProvider for the container
        $this->printInfoMessage('Generating MainServiceProvider');
        $this->call('apiato:generate:serviceprovider', [
            '--container'   => $containerName,
            '--file'        => 'MainServiceProvider',
            '--stub'        => 'maincontainerserviceprovider',
        ]);

        // create the Attributes Interface for the model
        $this->printInfoMessage('Generating Attributes');
        $this->call('apiato:generate:attributes', [
            '--container'   => $containerName,
            '--file'        => $containerName.'Attributes'
        ]);

        // create the model and repository for this container
        $this->printInfoMessage('Generating Model');
        $this->call('apiato:generate:mongomodel', [
            '--container'   => $containerName,
            '--file'        => $model,
            '--tablename'   => $models,
        ]);

        // create the migration file for the model
        $this->printInfoMessage('Generating a basic Migration file');
        $this->call('apiato:generate:migration:mongo', [
            '--container'   => $containerName,
            '--file'        => 'create_' . Str::lower($_containerName) . '_collection',
            '--tablename'   => $models,
        ]);

        // create a transformer for the model
        $this->printInfoMessage('Generating Actions for the Schema');
        $this->call('apiato:generate:schema:actions', [
            '--container'   => $containerName,
            '--file'        => $containerName . 'Actions',
        ]);

        // create the Schema for the model
        $this->printInfoMessage('Generating Schema');
        $this->call('apiato:generate:schema', [
            '--container'   => $containerName,
            '--file'        => $containerName.'Schema'
        ]);

        // create the Layer for the container
        $this->printInfoMessage('Generating Layer');
        $this->call('apiato:generate:layer', [
            '--container'   => $containerName,
            '--file'        => $containerName.'Layer'
        ]);

        // create the factory for the container
        $this->printInfoMessage('Generating Factory');
        $this->call('apiato:generate:factory', [
            '--container'   => $containerName,
            '--file'        => $containerName.'Factory'
        ]);

        // create a transformer for the model
        $this->printInfoMessage('Generating Transformer for the Model');
        $this->call('apiato:generate:transformer', [
            '--container'   => $containerName,
            '--file'        => $containerName . 'Transformer',
            '--model'       => $model,
            '--full'        => false,
        ]);

        // create the default routes for this container
        $this->printInfoMessage('Generating Default Routes');
        $version = $this->checkParameterOrAsk('docversion', 'Enter the version for *all* API endpoints (integer)', 1);
        $doctype = $this->checkParameterOrChoice('doctype', 'Select the type for *all* endpoints', ['private', 'public'], 0);

        // get the URI and remove the first trailing slash
        $url = Str::lower($this->checkParameterOrAsk('url', 'Enter the base URI for all endpoints (foo/bar)', Str::lower($models)));
        $url = ltrim($url, '/');

        $this->printInfoMessage('Creating Requests for Routes');
        $this->printInfoMessage('Generating Default Actions');
        $this->printInfoMessage('Generating Default Tasks');

        $routes = [
            [
                'stub'        => 'GetAll',
                'name'        => 'GetAll' . $models,
                'operation'   => 'getAll' . $models,
                'verb'        => 'GET',
                'url'         => $url,
                'request'     => 'GetAll' . $models . 'Request'
            ],
            [
                'stub'        => 'Find',
                'name'        => 'Find' . $model . 'ById',
                'operation'   => 'find' . $model . 'ById',
                'verb'        => 'GET',
                'url'         => $url . '/{id}',
                'request'     => 'Find' . $model . 'ById' . 'Request'
            ],
            [
                'stub'        => 'Create',
                'name'        => 'Create' . $model,
                'operation'   => 'create' . $model,
                'verb'        => 'POST',
                'url'         => $url,
                'request'     => 'Create' . $model . 'Request'
            ],
            [
                'stub'        => 'Update',
                'name'        => 'Update' . $model,
                'operation'   => 'update' . $model,
                'verb'        => 'PATCH',
                'url'         => $url . '/{id}',
                'request'     => 'Update' . $model . 'Request'
            ],
            [
                'stub'        => 'Delete',
                'name'        => 'Delete' . $model,
                'operation'   => 'delete' . $model,
                'verb'        => 'DELETE',
                'url'         => $url . '/{id}',
                'request'     => 'Delete' . $model . 'Request'
            ],
        ];

        foreach ($routes as $route)
        {
            $this->call('apiato:generate:route', [
                '--container' => $containerName,
                '--file' => $route['name'],
                '--ui' => $ui,
                '--operation' => $route['operation'],
                '--doctype' => $doctype,
                '--docversion' => $version,
                '--url' => $route['url'],
                '--verb' => $route['verb'],
            ]);

            $this->call('apiato:generate:request', [
                '--container' => $containerName,
                '--file' => $route['request'],
                '--ui' => $ui,
                '--transporter' => false,
                '--transportername' => 'none',
            ]);
        }

        // finally generate the controller
        $this->printInfoMessage('Generating Controller to wire everything together');
        $this->call('apiato:generate:controller', [
            '--container'   => $containerName,
            '--file'        => 'Controller',
            '--ui'          => $ui,
            '--stub'        => 'CRUD.SERVICE.API',
        ]);

        $this->printInfoMessage('Generating Composer File');
        return [
            'path-parameters' => [
                'container-name' => $containerName,
            ],
            'stub-parameters' => [
                '_container-name' => $_containerName,
                'container-name' => $containerName,
                'class-name' => $this->fileName,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    /**
     * Get the default file name for this component to be generated
     *
     * @return string
     */
    public function getDefaultFileName()
    {
        return 'composer';
    }

    public function getDefaultFileExtension()
    {
        return 'json';
    }

}
