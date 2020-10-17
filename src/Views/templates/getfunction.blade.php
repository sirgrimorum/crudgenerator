    //
    /**
    * Get the flied value using the configuration array
    * 
    * {{"@"}}param string $key The field to return
    * {{"@"}}param boolean $justValue Optional If return just the formated value (true) or an array with 3 elements, label, value and data (detailed data for the field)
    * {{"@"}}return mixed
    */
    public function get($key, $justValue = true) {
        $celda = \Sirgrimorum\CrudGenerator\CrudGenerator::field_array($this, $key);
        if ($justValue){
            return $celda['value'];
        }else{
            return $celda;
        }
    }

