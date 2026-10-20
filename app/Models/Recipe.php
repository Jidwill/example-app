<?php

namespace App\Models;

   use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

   #[Fillable(['title', 'protein'])]
   class Recipe extends Model
{
    use HasFactory;
}
