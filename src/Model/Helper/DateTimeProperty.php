<?php

namespace Neoan\Model\Helper;

use Neoan\Enums\TimePeriod;
use Neoan\Errors\SystemError;
use Neoan\Helper\DateHelper;

class DateTimeProperty
{
    public ?string $value = null;
    public ?int $stamp = null;
    public DateHelper $dateTime;
    public function __construct(?string $existingValue = null)
    {
        if($existingValue){
            $this->set($existingValue);
        }
    }
    public function toNull(): void
    {
        $this->value = null;
        $this->stamp = null;
    }
    public function addTimePeriod(int $amount, TimePeriod $period): self
    {
        $this->ensureHelper();
        $interVal = new \DateInterval($period->getPeriod($amount));
        $this->dateTime->add($interVal);
        $this->update();
        return $this;
    }

    public function getTimeDifference(DateHelper $difference)
    {
        $this->ensureHelper();
        return $this->dateTime->diff($difference);
    }

    public function set(mixed $dateHelperInput):void
    {
        try{
            $this->dateTime = new DateHelper($dateHelperInput);
            $this->update();
        }catch (\Exception $e) {
            new SystemError('Unprocessable DateTime-format: '. $dateHelperInput);
        }
    }

    public function subtractTimePeriod(int $amount, TimePeriod $period): self
    {
        $this->ensureHelper();
        $interVal = new \DateInterval($period->getPeriod($amount));
        $this->dateTime->sub($interVal);
        $this->update();
        return $this;
    }
    public function __toString(): string
    {
        if($this->value){
            return (string) $this->dateTime;
        }
        return '';
    }
    private function update(): void
    {
        $this->value = (string) $this->dateTime;
        $this->stamp = $this->dateTime->getTimestamp();
    }
    private function ensureHelper(): void
    {
        if(!isset($this->dateTime)){
            $this->dateTime = new DateHelper();
            $this->value = (string) $this->dateTime;
            $this->stamp = $this->dateTime->getTimestamp();
        }
    }

}