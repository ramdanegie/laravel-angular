<?php

namespace App;

use App\Exceptions\DataNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;
class BaseModel extends Model
{
    protected $transformerPath=null;
    /**
     * @return mixed
     */
    public function getTransformerPath()
    {
        return $this->transformerPath;
    }

    /**
     * @param mixed $transformerPath
     */
    public function setTransformerPath($transformerPath)
    {
        $this->transformerPath = $transformerPath;
    }


    public static function findOrThrowException($id, $msg = '', $columns = ['*'])
    {
        $result = static::find($id, $columns);

        if (is_array($id)) {
            if (count($result) == count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        throw new DataNotFoundException($msg);
    }

    public function canEdit(){
        return true;
    }

    public function canDelete(){
        return true;
    }


    protected static function boot()
    {
        parent::boot();

//        static::creating(function ($model) {
//            $model->norec = (string)$model->generateNewId();
//        });
    }

    public function generateNewId()
    {
        return substr(Uuid::generate(), 0, 32);
    }



}
