<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'location','description'
    ];

    /**
     * Get all employee details for this department
     */
    public function employeeDetails()
    {
        return $this->hasMany(EmployeeDetail::class);
    }

    /**
     * Get all users (employees) in this department
     */
    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            EmployeeDetail::class,
            'department_id', // Foreign key on employee_details table
            'id', // Foreign key on users table
            'id', // Local key on departments table
            'user_id' // Local key on employee_details table
        );
    }
}
