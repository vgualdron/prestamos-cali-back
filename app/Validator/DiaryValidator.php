<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class DiaryValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());
        }

        private function rules(){
            return[
                'user_id' => 'required',
                'date' => 'required',
            ];
        }

        private function messages(){
            return [
                'user_id.required' => 'El id de usuario es requerido',
                'date.required' => 'La fecha es requerida',
            ];
        }
    }
?>
