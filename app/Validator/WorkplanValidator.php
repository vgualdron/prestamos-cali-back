<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class WorkplanValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());
        }

        private function rules(){
            return[
            ];
        }

        private function messages(){
            return [
            ];
        }
    }
?>
