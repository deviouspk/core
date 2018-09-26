<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ModelGenerator
 *
 * @author  Justin Atack  <justinatack@gmail.com>
 */
class MongoModelGenerator extends GeneratorCommand implements ComponentsGenerator
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:mongomodel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Mongo Model class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $fileType = 'Model';

    /**
     * The structure of the file path.
     *
     * @var  string
     */
    protected $pathStructure = '{container-name}/Models/*';

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
    protected $stubName = 'mongomodel.stub';

    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['tablename', null, InputOption::VALUE_OPTIONAL, 'The name for the database collection'],
    ];

    /**
     * @return array|null
     */
    public function getUserInputs()
    {
        $tablename = Str::lower($this->checkParameterOrAsk('tablename', 'Enter the name of the database collection for the model'));
        return [
            'path-parameters' => [
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_container-name' => Str::lower($this->containerName),
                'container-name' => $this->containerName,
                'class-name' => $this->fileName,
                'resource-key' => strtolower(Pluralizer::plural($this->fileName)),
                'table-name' => $tablename
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

}
