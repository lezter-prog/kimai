<?php

namespace App\Model;

use App\Entity\Activity;
use App\Entity\Project;
use App\Entity\Timesheet;
use App\Entity\User;
use App\Entity\Tag;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class TimesheetNewModel 
{
    private ?int $id = null;
    private ?DateTime $date = null;
    private ?string $timezone = null;
    private bool $localized = false;
    private ?DateTime $begin =null;
    private ?\DateTime $end =null;

    private ?int $duration = 0;

    private ?User $user =null;
    
    private ?string $activity ;
    
    private ?Project $project ;
    
    private ?string $description ;
    
    private ?float $rate = 0.00;

    private ?float $internalRate =0.00;
    
    private ?float $fixedRate =null ;

    private ?float $hourlyRate =null ;
    
    private ?bool $billable =null;
    private ?Collection $tags;
    private ?Collection $meta;
    
  
   

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->meta = new ArrayCollection();
    }
    protected function localizeDates(): void
    {
        if ($this->localized) {
            return;
        }

        if (null !== $this->begin) {
            $this->begin->setTimezone(new DateTimeZone($this->timezone));
        }

        if (null !== $this->end) {
            $this->end->setTimezone(new DateTimeZone($this->timezone));
        }

        $this->localized = true;
    }

    public function getBegin(): ?DateTime
    {
        $this->localizeDates();

        return $this->begin;
    }

    public function setBegin(DateTime $begin): TimesheetNewModel
    {
        
        $this->begin = $begin;
        $this->timezone = $begin->getTimezone()->getName();
        // make sure that the original date is always kept in UTC
        $this->date = new DateTime($begin->format('Y-m-d 00:00:00'), new DateTimeZone('UTC'));
        return $this;
    }

    public function getEnd(): ?DateTime
    {
        $this->localizeDates();

        return $this->end;
    }

    public function isRunning(): bool
    {
        return $this->end === null;
    }

    /**
     * @param DateTime $end
     * @return TimesheetNewModel
     */
    public function setEnd(?DateTime $end): TimesheetNewModel
    {
        $this->end = $end;

        if (null === $end) {
            $this->duration = 0;
            $this->rate = 0.00;
        } else {
            $this->timezone = $end->getTimezone()->getName();
        }

        return $this;
    }

    /**
     * @param int|null $duration
     * @return TimesheetNewModel
     */
    public function setDuration(?int $duration): TimesheetNewModel
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Do not rely on the results of this method for running records.
     *
     * @param bool $calculate
     * @return int|null
     */
    public function getDuration(bool $calculate = true): ?int
    {
        // only auto calculate if manually set duration is null - the result is important for eg. validations
        if ($calculate && $this->duration === null && $this->begin !== null && $this->end !== null) {
            return $this->getCalculatedDuration();
        }

        return $this->duration;
    }

    public function getCalculatedDuration(): ?int
    {
        if ($this->begin !== null && $this->end !== null) {
            return $this->end->getTimestamp() - $this->begin->getTimestamp();
        }

        return null;
    }

    public function setUser(?User $user): TimesheetNewModel
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setActivity(?string $activity): TimesheetNewModel
    {
        $this->activity = $activity;

        return $this;
    }

    public function getActivity(): ?string
    {
        return $this->activity;
    }

    public function setActivities(?string $activities): TimesheetNewModel
    {
        $this->activities = $activities;

        return $this;
    }

    public function getActivities(): ?string
    {
        return $this->activities;
    }
    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): TimesheetNewModel
    {
        $this->project = $project;

        return $this;
    }

    public function setDescription(?string $description): TimesheetNewModel
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param float $rate
     * @return TimesheetNewModel
     */
    public function setRate($rate): TimesheetNewModel
    {
        $this->rate = $rate;

        return $this;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function setInternalRate(?float $rate): TimesheetNewModel
    {
        $this->internalRate = $rate;

        return $this;
    }

    public function getInternalRate(): ?float
    {
        return $this->internalRate;
    }

    public function addTag(Tag $tag): TimesheetNewModel
    {
        if ($this->tags->contains($tag)) {
            return $this;
        }
        $this->tags->add($tag);

        return $this;
    }

    public function removeTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            return;
        }
        $this->tags->removeElement($tag);
    }

    /**
     * @return Collection<Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @return string[]
     */
    public function getTagsAsArray(): array
    {
        /** @var array<Tag> $tags */
        $tags = $this->getTags()->toArray();

        return array_map(
            function ($element) {
                return (string) $element->getName();
            },
            $tags
        );
    }

    /**
     * @return bool
     */
    public function isExported(): bool
    {
        return $this->exported;
    }

    /**
     * @param bool $exported
     * @return TimesheetNewModel
     */
    public function setExported(bool $exported): TimesheetNewModel
    {
        $this->exported = $exported;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * BE WARNED: this method should NOT be used from outside.
     * It is reserved for some very rare use-cases.
     *
     * @internal
     * @param string $timezone
     * @return TimesheetNewModel
     */
    public function setTimezone(string $timezone): TimesheetNewModel
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * This method returns ALWAYS: "timesheet"
     *
     * @return string
     */
    public function getType(): string
    {
        return 'timesheet';
    }

    public function getAmount(): float
    {
        return 1.0;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): TimesheetNewModel
    {
        $allowed = [self::WORK, self::HOLIDAY, self::SICKNESS, self::PARENTAL, self::OVERTIME];

        if (!\in_array($category, $allowed)) {
            throw new \InvalidArgumentException(sprintf('Invalid timesheet category "%s" given, expected one of: %s', $category, implode(', ', $allowed)));
        }

        $this->category = $category;

        return $this;
    }

    public function isBillable(): bool
    {
        return $this->billable;
    }

    public function getBillable(): bool
    {
        return $this->billable;
    }

    public function setBillable(bool $billable): TimesheetNewModel
    {
        $this->billable = $billable;

        return $this;
    }

    public function getBillableMode(): ?string
    {
        return $this->billableMode;
    }

    public function setBillableMode(?string $billableMode): void
    {
        $this->billableMode = $billableMode;
    }

    public function getFixedRate(): ?float
    {
        return $this->fixedRate;
    }

    public function setFixedRate(?float $fixedRate): TimesheetNewModel
    {
        $this->fixedRate = $fixedRate;

        return $this;
    }

    public function getHourlyRate(): ?float
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?float $hourlyRate): TimesheetNewModel
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    public function getModifiedAt(): \DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeImmutable $dateTime): void
    {
        $this->modifiedAt = $dateTime;
    }

    /**
     * @return Collection|MetaTableTypeInterface[]
     */
    public function getMetaFields(): Collection
    {
        return $this->meta;
    }

    /**
     * @return MetaTableTypeInterface[]
     */
    public function getVisibleMetaFields(): array
    {
        $all = [];
        foreach ($this->meta as $meta) {
            if ($meta->isVisible()) {
                $all[] = $meta;
            }
        }

        return $all;
    }

    public function resetRates(): void
    {
        $this->setRate(0.00);
        $this->setInternalRate(null);
        $this->setHourlyRate(null);
        $this->setFixedRate(null);
        $this->setBillableMode(Timesheet::BILLABLE_AUTOMATIC);
    }

    public function getMetaField(string $name): ?MetaTableTypeInterface
    {
        foreach ($this->meta as $field) {
            if (strtolower($field->getName()) === strtolower($name)) {
                return $field;
            }
        }

        return null;
    }

    public function setMetaField(MetaTableTypeInterface $meta): EntityWithMetaFields
    {
        if (null === ($current = $this->getMetaField($meta->getName()))) {
            $meta->setEntity($this);
            $this->meta->add($meta);

            return $this;
        }

        $current->merge($meta);

        return $this;
    }

    public function createCopy(?TimesheetNewModel $timesheet = null): TimesheetNewModel
    {
        if (null === $timesheet) {
            $timesheet = new TimesheetNewModel();
        }

        $values = get_object_vars($this);
        foreach ($values as $k => $v) {
            $timesheet->$k = $v;
        }

        $timesheet->meta = new ArrayCollection();

        /** @var TimesheetMeta $meta */
        foreach ($this->meta as $meta) {
            $timesheet->setMetaField(clone $meta);
        }

        $timesheet->tags = new ArrayCollection();

        /** @var Tag $tag */
        foreach ($this->tags as $tag) {
            $timesheet->addTag($tag);
        }

        return $timesheet;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }

        // field will not be set, if it contains a value
        $this->modifiedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->exported = false;

        $currentMeta = $this->meta;
        $this->meta = new ArrayCollection();
        /** @var TimesheetMeta $meta */
        foreach ($currentMeta as $meta) {
            $newMeta = clone $meta;
            $newMeta->setEntity($this);
            $this->setMetaField($newMeta);
        }

        $currentTags = $this->tags;
        $this->tags = new ArrayCollection();
        /** @var Tag $tag */
        foreach ($currentTags as $tag) {
            $this->addTag($tag);
        }
    }
}
