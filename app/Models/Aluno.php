<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    protected $fillable = [
        'nome', 
        'email', 
        'telefone', 
        'endereco', 
        'cep', 
        'cpf', 
        'rg', 
        'data_nascimento', 
        'sexo'
    ];  
}
