<?php

namespace App\Http\Livewire\Ui;

use Livewire\Component;

class SidebarPreview extends Component
{
    public $showEmployees = true;
    public $showWorkShifts = true;
    public $showSchedules = true;
    public $showAttendance = true;
    public $showCorrections = true;

    public function toggle($field)
    {
        if (property_exists($this, $field)) {
            $this->{$field} = ! $this->{$field};
        }
    }

    public function render()
    {
        return view('components.ui.sidebar-preview');
    }
}
