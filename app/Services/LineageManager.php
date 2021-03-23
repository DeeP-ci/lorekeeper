<?php namespace App\Services;

use App\Services\Service;

use Carbon\Carbon;

use DB;

use App\Models\User\User;
use App\Models\Character\Character;
use App\Models\Character\CharacterLineage;
use App\Models\Character\CharacterLineageLink;

class LineageManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Lineage Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of lineage data.
    |
    */

    /**
     * Deletes a lineage
     *
     * @param  \App\Models\Character\CharacterLineage  $lineage
     * @param  array                                   $data
     * @param  \App\Models\User\User                   $user
     * @return  bool
     */
    public function deleteLineage($lineage, $data, $user)
    {
        DB::beginTransaction();
        try {
            if($lineage->id != $data['lineage_id']) throw new \Exception("Lineage ID mismatch, something's gone very wrong.");

            $lineage->parents()->delete();
            $lineage->children()->delete();
            $lineage->delete();

            if ($lineage->character_id != null)
                $this->createLog($user->id, null, null, null, $lineage->character_id, 'Lineage Deleted', '[#'.$lineage->id.']', 'character');

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Creates a character log.
     * Ripped directly from CharacterManager.
     *
     * @param  int     $senderId
     * @param  string  $senderUrl
     * @param  int     $recipientId
     * @param  string  $recipientUrl
     * @param  int     $characterId
     * @param  string  $type
     * @param  string  $data
     * @param  string  $logType
     * @param  bool    $isUpdate
     * @param  string  $oldData
     * @param  string  $newData
     * @return bool
     */
    public function createLog($senderId, $senderUrl, $recipientId, $recipientUrl, $characterId, $type, $data, $logType, $isUpdate = false, $oldData = null, $newData = null)
    {
        return DB::table($logType == 'character' ? 'character_log' : 'user_character_log')->insert(
            [
                'sender_id' => $senderId,
                'sender_url' => $senderUrl,
                'recipient_id' => $recipientId,
                'recipient_url' => $recipientUrl,
                'character_id' => $characterId,
                'log' => $type . ($data ? ' (' . $data . ')' : ''),
                'log_type' => $type,
                'data' => $data,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ] + ($logType == 'character' ?
                [
                    'change_log' => $isUpdate ? json_encode([
                        'old' => $oldData,
                        'new' => $newData
                    ]) : null
                ] : [])
        );
    }
}
