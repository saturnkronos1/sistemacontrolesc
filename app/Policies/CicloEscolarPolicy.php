<?php

namespace App\Policies;

use App\Models\CicloEscolar;
use App\Models\User;

class CicloEscolarPolicy
{
    /**
     * Determine who can modify a cycle (edit, activate) — only Superadmin can touch finalizado.
     */
    public function modifyStatus(User $user, CicloEscolar $ciclo): bool
    {
        if ($ciclo->estatus === 'finalizado') {
            return $user->hasRole('Superadmin');
        }

        return $user->hasAnyRole(['Superadmin', 'Director']);
    }

    /**
     * Only Superadmin can revert a finalizado cycle.
     */
    public function revert(User $user, CicloEscolar $ciclo): bool
    {
        return $user->hasRole('Superadmin');
    }
}
