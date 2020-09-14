<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToArray;

class CommonImport implements ToArray
{
    /**
    * @param Collection $collection
    */
//    public function collection(Collection $collection)
//    {
//        //
//    }

      public function Array(Array $tables){
          return $tables;
      }
}
