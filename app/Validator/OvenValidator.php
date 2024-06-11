<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class OvenValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'name' => 'required|min:1|max:30|unique:ovens,name,'.$this->data['id'].',id,batterie,'.$this->data['batterie'],
            ];
        }

        private function messages(){
            return [
                'name.required' => 'El nombre es requerido',
                'name.unique' => 'El nombre "'.$this->data['name'].'", ya existe',               
                'name.min' => 'El nombre debe tener un mínimo de 1 caracteres',
                'name.max' => 'El nombre debe tener un máximo de 30 caracteres',
                'batterie' => 'required|exists:batterie,id',
            ];
        }
    }
?>