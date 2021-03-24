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
     * Creates a lineage
     *
     * @param  \App\Models\Character\CharacterLineage  $lineage
     * @param  array                                   $data
     * @param  \App\Models\User\User                   $user
     * @return  bool
     */
    public function createLineage($data, $user)
    {
        DB::beginTransaction();
        try {
            if($data['owner_id']) {
                // Check if there is a character id set. If there is, check they do not have a lineage.
                $character = Character::where('id', $data['owner_id'])->first();
                if (!$character) throw new \Exception("Couldn't find a character with that ID.");

                $lineage = CharacterLineage::where('character_id', $data['owner_id'])->first();
                if ($lineage) throw new \Exception("Couldn't create a lineage for this character, as they already have one.");

                $lineage = CharacterLineage::create(['character_id' => $data['owner_id']]);
                if (!$lineage) throw new \Exception("Something went wrong when trying to create a lineage.");
            } else {
                // Rogues need names.
                if (!$data['owner_name']) throw new \Exception("Rogue lineages need a name.");

                $lineage = CharacterLineage::create(['character_name' => $data['owner_name']]);
                if (!$lineage) throw new \Exception("Something went wrong when trying to create a lineage.");
            }

            // Attatch the Lineage Links
            foreach($data['parent_type'] as $key => $type) {
                $parentLineage = false;

                if($type == "Character") {
                    // Finds the first character that is not a myo slot with the ID specified.
                    $parent = Character::where('is_myo_slot', false)->where('id', $data['parent_data'][$key])->first();
                    $parentLineage = (!$parent) ? false : ((!$parent->lineage) ? CharacterLineage::create(['character_id' => $parent->id]) : $parent->lineage);
                } else if($type == "Rogue") {
                    // Finds the first lineage with the specified id.
                    $parentLineage = CharacterLineage::where('id', $data['parent_data'][$key])->first();
                } else if($type == "New") {
                    // Create a lineage if there's data for it.
                    if($data['parent_data'][$key] != "")
                        $parentLineage = CharacterLineage::create(['character_name' => $data['parent_data'][$key]]);
                }

                if ($parentLineage) {
                    $link = CharacterLineageLink::create(['lineage_id' => $lineage->id, 'parent_lineage_id' => $parentLineage->id]);
                }
            }

            if ($lineage->character_id != null)
                $this->createLog($user->id, null, null, null, $lineage->character_id, 'Lineage Created', '[#'.$lineage->id.']', 'character');

            return $this->commitReturn($lineage);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Edits a lineage
     *
     * @param  \App\Models\Character\CharacterLineage  $lineage
     * @param  array                                   $data
     * @param  \App\Models\User\User                   $user
     * @return  bool
     */
    public function editLineage($lineage, $data, $user)
    {
        DB::beginTransaction();
        try {
            if($data['owner_id']) {
                // If the character ID != new ID, we're changing owners.
                if ($lineage->character_id != $data['owner_id']) {
                    $character = Character::where('id', $data['owner_id'])->first();
                    if (!$character) throw new \Exception("Couldn't find a character with that ID.");

                    if (!$lineage->character_id && $character->is_myo_slot) throw new \Exception("Cannot set a MYO Slot as the new owner of this lineage.");

                    $test = CharacterLineage::where('character_id', $data['owner_id'])->first();
                    if ($test) throw new \Exception("Couldn't move the lineage to that character, as they already have a lineage.");
                }
                $lineage->character_id = $data['owner_id'];
                $lineage->character_name = null;
            } else {
                // Rogues need names.
                if (!$data['owner_name']) throw new \Exception("Rogue lineages need a name.");

                $lineage->character_id = null;
                $lineage->character_name = $data['owner_name'];
            }
            if (!$lineage) throw new \Exception("Something went wrong when trying to update ownership.");

            $lineage->parents()->delete();
            foreach($data['parent_type'] as $key => $type) {
                $parentLineage = false;

                if($type == "Character") {
                    // Finds the first character that is not a myo slot with the ID specified.
                    $parent = Character::where('is_myo_slot', false)->where('id', $data['parent_data'][$key])->first();
                    $parentLineage = (!$parent) ? false : ((!$parent->lineage) ? CharacterLineage::create(['character_id' => $parent->id]) : $parent->lineage);
                } else if($type == "Rogue") {
                    // Finds the first lineage with the specified id.
                    $parentLineage = CharacterLineage::where('id', $data['parent_data'][$key])->first();
                } else if($type == "New") {
                    // Create a lineage if there's data for it.
                    if($data['parent_data'][$key] != "")
                        $parentLineage = CharacterLineage::create(['character_name' => $data['parent_data'][$key]]);
                }

                if ($parentLineage) {
                    $link = CharacterLineageLink::create(['lineage_id' => $lineage->id, 'parent_lineage_id' => $parentLineage->id]);
                }
            }

            $lineage->children()->delete();
            if(true || ($lineage->character && !$lineage->character->is_myo_slot)) {
                foreach($data['child_type'] as $key => $type) {
                    $childLineage = false;
                    if($type == "Character") {
                        // Finds the first character or myo with the ID specified.
                        $child = Character::where('id', $data['child_data'][$key])->first();
                        $childLineage = (!$child) ? false : ((!$child->lineage) ? CharacterLineage::create(['character_id' => $child->id]) : $child->lineage);
                    } else if($type == "Rogue") {
                        // Finds the first lineage with the specified id.
                        $childLineage = CharacterLineage::where('id', $data['child_data'][$key])->first();
                    } else if($type == "New") {
                        // Create a lineage if there's data for it.
                        if($data['child_data'][$key] != "")
                            $childLineage = CharacterLineage::create(['character_name' => $data['child_data'][$key]]);
                    }
    
                    if ($childLineage) {
                        $link = CharacterLineageLink::create(['lineage_id' => $childLineage->id, 'parent_lineage_id' => $lineage->id]);
                    }
                }
            }

            // Save and update the logs.
            $lineage->save();
            if ($lineage->character_id != null)
                $this->createLog($user->id, null, null, null, $lineage->character_id, 'Lineage Edited', '[#'.$lineage->id.']', 'character');

            return $this->commitReturn($lineage);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

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
