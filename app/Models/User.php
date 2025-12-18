<?php

namespace App\Models;

use App\Enums\BanType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The guard name for this model.
     *
     * @var string
     */
    protected string $guard_name = 'api'; // Explicitly set the guard name

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'email_verified_at',
        'role',
        'is_banned',
        'banned_until',
        'city',
        'profession',
        'bio',
        'weight',
        'height',
        'age'
    ];

    protected $casts = [
//        'interests' => 'array',
//        'preferences' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Ensure the 'role' attribute is always included in the model's array and JSON representation.
     *
     * @var array
     */
    protected $appends = ['role', 'photo'];

    // Define the relationship to the images table (Many-to-Many)
    public function photos()
    {
        return $this->belongsToMany(File::class, 'file_user', 'user_id', 'file_id');
    }

    // Define the relationship with Detail model via the detail_user pivot table
    public function details()
    {
        return $this->belongsToMany(Detail::class, 'detail_user', 'user_id', 'detail_id');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user', 'user_id', 'conversation_id');
    }

    public function swipeMatches()
    {
        $userId = auth()->id(); // Get the current authenticated user's ID

        // Get swipe matches where swipe_id_1 is the current user
        $swipes1 = $this->hasMany(SwipeMatch::class, 'swipe_id_1', 'id')
            ->where('swipe_id_1', $userId)
            ->with(['conversation.users' => function ($query) use ($userId) {
                // Fetch users in the conversation, but exclude the authenticated user
                $query->where('users.id', '!=', $userId);
            }])
            ->get(); // Fetch the collection

        // Get swipe matches where swipe_id_2 is the current user
        $swipes2 = $this->hasMany(SwipeMatch::class, 'swipe_id_2', 'id')
            ->where('swipe_id_2', $userId)
            ->with(['conversation.users' => function ($query) use ($userId) {
                // Fetch users in the conversation, but exclude the authenticated user
                $query->where('users.id', '!=', $userId);
            }])
            ->get(); // Fetch the collection

        // Concatenate the two collections
        return $swipes1->concat($swipes2);
    }

    /**
     * Get all messages sent by the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get all messages received by the user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * The files that belong to the user.
     */
    public function files()
    {
        return $this->belongsToMany(File::class, 'file_user', 'user_id', 'file_id');
    }

    /**
     * Fetch all the details (groups, subgroups, and options) with eager loading.
     *
     * @return \Illuminate\Support\Collection
     */
    private function fetchDetails()
    {
        // Eager load subgroups and options in a single query to prevent N+1
        return Detail::with('children.children')  // Eager load children (subgroups) and children of children (options)
        ->whereNull('parent_id')  // Only get top-level groups (no parent)
        ->get();
    }

    /**
     * Map the details into a specific structure while optimizing selection checks.
     *
     * @param \Illuminate\Support\Collection $details
     * @param array $selectedDetailsIds
     * @param bool $includeSelection
     * @return array
     */
    private function mapDetails($details, array $selectedDetailsIds = [], bool $includeSelection = true): array
    {
        $selectedDetailsSet = collect($selectedDetailsIds); // Use a collection for faster contains lookup

        return $details->map(function ($group) use ($selectedDetailsSet, $includeSelection) {
            $subGroups = $group->children->map(function ($subGroup) use ($group, $selectedDetailsSet, $includeSelection) {
                // Check if this subgroup has its own subgroups (options)
                $subGroupOptions = $subGroup->children->map(function ($option) use ($selectedDetailsSet, $includeSelection) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                        'is_selected' => $includeSelection ? $selectedDetailsSet->contains($option->id) : null,
                    ];
                });

                // If there are no subgroups (options) for this subgroup, return main group options
                if ($subGroupOptions->isEmpty()) {
                    $subGroupOptions = collect([[
                        'id' => $group->id,
                        'name' => $group->name,
                        'is_selected' => $includeSelection ? $selectedDetailsSet->contains($group->id) : null,
                    ]]);
                }

                return [
                    'id' => $subGroup->id,
                    'name' => $subGroup->name,
                    'options' => $subGroupOptions->toArray(),
                ];
            });

            // For groups like "Gender", return options without subgroups
            if ($group->children->isNotEmpty() && $group->children->first()->children->isEmpty()) {
                return [
                    'id' => $group->id,
                    'group' => $group->name,
                    'options' => $group->children->map(function ($option) use ($selectedDetailsSet, $includeSelection) {
                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'is_selected' => $includeSelection ? $selectedDetailsSet->contains($option->id) : null,
                        ];
                    })->toArray(),
                ];
            }

            return [
                'id' => $group->id,
                'group' => $group->name,
                'subGroups' => $subGroups->isEmpty() ? null : $subGroups->toArray(),
            ];
        })->toArray();
    }

    /**
     * Fetch all selected details and map them.
     *
     * @return array
     */
    public function allSelectedDetails(): array
    {
        // Fetch selected details for the user efficiently
        $selectedDetailsIds = $this->details()->pluck('id')->toArray(); // Use pluck for faster retrieval of IDs

        // Fetch the details and map them
        $details = $this->fetchDetails();
        return $this->mapDetails($details, $selectedDetailsIds);
    }

    public function blockedUsers()
    {
        return $this->hasMany(Block::class, 'blocker_id')->with('blocked');
    }

    public function blockedByUsers()
    {
        return $this->hasMany(Block::class, 'blocked_id')->with('blocker');
    }

    public function hasBlocked(User $user)
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }


    public function isBlockedBy(User $user)
    {
        return $this->blockedByUsers()->where('blocker_id', $user->id)->exists();
    }

    // Relationship to user details (preferences)
    public function detailPreferences(): BelongsToMany
    {
        return $this->belongsToMany(
            Detail::class,
            'user_detail_preference',
            'user_id',
            'detail_id'
        );
    }


    // Relationship to user preference data (age, height, etc.)
    public function preferenceData(): HasOne
    {
        return $this->hasOne(UserDataPreference::class, 'user_id', 'id');
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
//        $this->notify(new ResetPasswordNotification($token));
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function linkedSocialAccounts(): HasOne
    {
        return $this->hasOne(LinkedSocialAccount::class);
    }


    /**
     * Check if the user is banned, either permanently or temporarily.
     *
     * @return BanType
     */
    public function banType(): BanType
    {
        // Check if the user is banned permanently
        if ($this->isPermanentlyBanned()) {
            return BanType::PERMANENT;
        }

        // Check if the user is banned temporarily
        if ($this->isTemporarilyBanned()) {
            return BanType::TEMPORARY;
        }

        return BanType::NON_BANNED;
    }

    /**
     * Determine if the user is permanently banned.
     *
     * @return bool
     */
    public function isPermanentlyBanned(): bool
    {
        return $this->is_banned && !$this->banned_until;
    }

    /**
     * Determine if the user is temporarily banned.
     *
     * @return bool
     */
    public function isTemporarilyBanned(): bool
    {
        return $this->is_banned && $this->banned_until && Carbon::now()->lessThanOrEqualTo($this->banned_until);
    }

    /**
     * Find a user by their email address.
     *
     * @param string $email
     * @return \App\Models\User|null
     */
    public static function findByEmail(string $email): ?User
    {
        return self::where('email', $email)->first();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function banUser(int $banType, $bannedUntil): void
    {
        if ($banType == BanType::TEMPORARY->value) {
            // Calculate the duration for a temporary ban
            //$banUntil = Carbon::now()->addDays($request->duration);
            $this->update([
                'is_banned' => true,
                'banned_until' => $bannedUntil
            ]);
        } else {
            // Permanent ban
            $this->update([
                'is_banned' => true,
                'banned_until' => null
            ]);
        }
    }

    /**
     * Accessor to get the user's primary role.
     *
     * @return string|null
     */
    public function getRoleAttribute(): ?string
    {
        return $this->roles->pluck('name')->first(); // Assuming a user has one primary role
    }

    /**
     * Mutator to assign a role when setting the 'role' attribute.
     *
     * @param string $role
     * @return void
     */
    public function setRoleAttribute(string $role): void
    {
        $this->syncRoles([$role]); // Replace existing roles with the new one
    }

    /**
     * Accessor to get the user's primary photo URL.
     *
     * @return string|null
     */
    public function getPhotoAttribute(): ?string
    {
        return $this->photos->isEmpty() ? "" : $this->photos->first()->url;
    }

    // Define whereRoles scope
    public function scopeWhereRoles($query, array $roles)
    {
        return $query->whereHas('roles', function ($q) use ($roles) {
            $q->whereIn('name', $roles);
        });
    }
}
