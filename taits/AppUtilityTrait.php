<?php

namespace Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;

trait AppUtilityTrait
{
    /**
     *
     *
     * @param string $type
     * @param integer $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function identifyModel(string $type, int $id = null): Model|null
    {
        /** @var array $models */
        $models = Relation::morphMap();

        if(!array_key_exists($type, $models)){
            throw (new ModelNotFoundException());
        }

        /** @var \Illuminate\Support\Collection */
        $modelClass = collect($models)
        ->get($type);

        if(is_null($id)){
            return $modelClass;
        }

        return $modelClass::findOrFail($id);
    }

    /**
     * obfuscateEmail function
     *
     * @param string $email
     * @return string
     */
    public function obfuscateEmail(string $email): string
    {
        $em   = explode("@",$email);
        $name = implode('@', array_slice($em, 0, count($em)-1));
        $len  = floor(strlen($name)/2);

        return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);
    }

    /**
     * replaceUnderScoreBySpaces function
     *
     * @param string $role
     * @return string
     */
    public function replaceUnderScoreBySpaces(string $role): string
    {
        return ucwords(str_replace("_", " ", $role));
    }

    /**
     * convertIndexedArrayToAssociative function
     *
     * @param array $array
     * @return array
     */
    public function convertIndexedArrayToAssociative(array $array): array
    {
        return collect($array)
            ->combine(
                collect($array)
                ->map(
                    function(string $item){
                        if (strpos($item, '_')) {
                            // contains an underscore and is two words
                            return $this->replaceUnderScoreBySpaces($item);
                        }
                        return ucfirst(__($item));
                    }
                )
            )
            ->all();
    }

    /**
     * Get value of a translatable field
     *
     * @param Closure $closure
     * @return array
     */
    function getFakeTranslatableValue(Closure $closure): array
    {
        $locales = array_values(config('app.global.locales')) ?? [];
        $title = [];
        foreach ($locales as $locale) {
            $title[$locale] = $closure();
        }
        return $title;
    }

    /**
     * lastIndexOfExplodedFolder function
     *
     * @param string $folder
     * @return string
     */
    private function lastIndexOfExplodedFolder(string $folder) : string
    {
        $explodedFolder = explode('/', $folder);
        $lastIndexOfFolder = $explodedFolder[sizeof($explodedFolder) - 1];
        return strtolower(trim($lastIndexOfFolder));
    }
}
