<?php
namespace App\View\Components;

use Illuminate\View\Component;

class Spec extends Component
{
    public $value;
    public $unit;
    public $precision;

    public function __construct($value = null, $unit = null, $precision = null)
    {
        $this->value = $value;
        $this->unit = $unit;
        $this->precision = $precision;
    }

    public function render()
    {
        return view('components.spec');
    }

    public function formatted()
    {
        $value = $this->value;
        $unit = $this->unit;
        $precision = $this->precision;
        // Normalizează lipsa valorii
        if ($value === null) return '—';
        $raw = is_string($value) ? trim($value) : $value;
        if ($raw === '' || $raw === '-' || $raw === '–' || $raw === '—') return '—';
        // Dacă e numeric, formatează și atașează unitatea standard
        if (is_numeric($raw)) {
            $prec = $precision ?? ((floor($raw) == $raw) ? 0 : 1);
            $num  = number_format((float)$raw, $prec, ',', '');
            return $unit ? "$num $unit" : $num;
        }
        // STRING: elimină dublurile de unitate la final, apoi adaugă o singură unitate dacă e cerută
        $s = preg_replace('/\s+/u', ' ', $raw);
        if ($unit) {
            $pattern = '/(?:\s| )'.preg_quote($unit, '/').'$/iu';
            if (preg_match($pattern, $s)) {
                return $s;
            }
            $s = preg_replace('/('.preg_quote($unit,'/').')(?:\s*\1)+$/iu', '$1', $s);
            return "$s $unit";
        }
        $s = preg_replace('/\b([a-zăâîșț\/0-9\.-]+)\b(?:\s*\1)+$/iu', '$1', $s);
        return $s;
    }
}
