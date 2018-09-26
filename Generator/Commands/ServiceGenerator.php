<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ServiceProviderGenerator
 *
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 */
class ServiceGenerator extends GeneratorCommand implements ComponentsGenerator
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Service for a Container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $fileType = 'Service';

    /**
     * The structure of the file path.
     *
     * @var  string
     */
    protected $pathStructure = '{container-name}/Services/*';

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
    protected $stubName = 'service.stub';

    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
    ];

    /**
     * @return array
     */
    public function getUserInputs()
    {
        // create the ServiceContract for the service
        $this->printInfoMessage('Generating Schema');
        $this->call('apiato:generate:service:contract', [
            '--container' => $this->containerName,
            '--file' => $this->containerName . 'ServiceContract'
        ]);

        return [
            'path-parameters' => [
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_container-name' => Str::lower($this->containerName),
                'container-name' => $this->containerName,
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
        return 'Service';
    }
}
