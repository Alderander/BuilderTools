<?php

/**
 * Copyright 2018 CzechPMDevs
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace czechpmdevs\buildertools\editors;

use czechpmdevs\buildertools\BuilderTools;
use czechpmdevs\buildertools\editors\object\BlockList;
use czechpmdevs\buildertools\editors\object\EditorResult;
use pocketmine\Player;


/**
 * Class Canceller
 * @package buildertools\editors
 */
class Canceller extends Editor {

    /** @var BlockList[][] $undoData */
    public $undoData = [];

    /** @var BlockList[][] $redoData */
    public $redoData = [];

    /**
     * @return string $name
     */
    public function getName(): string {
        return "Canceller";
    }

    /**
     * @param Player $player
     * @param BlockList $blocks
     */
    public function addStep(Player $player, BlockList $blocks) {
        $this->undoData[$player->getName()][] = $blocks;
    }

    /**
     * @param Player $player
     * @return EditorResult|null
     */
    public function undo(Player $player): EditorResult {
        if(!isset($this->undoData[$player->getName()]) || count($this->undoData[$player->getName()]) == 0) {
            $player->sendMessage(BuilderTools::getPrefix()."§cThere are not actions to undo!");
            return new EditorResult(0, 0, true);
        }

        if(count($this->undoData[$player->getName()]) == 1 && isset($this->undoData[$player->getName()][0])) {
            $blockList = $this->undoData[$player->getName()][0];
        }
        else {
            $blockList = end($this->undoData[$player->getName()]);
        }

        /** @var Filler $filler */
        $filler = BuilderTools::getEditor(static::FILLER);
        $result = $filler->fill($player, $blockList, [
            "saveUndo" => false,
            "saveRedo" => true
        ]);

        $this->addRedo($player, $last = array_pop($this->undoData[$player->getName()]));
        return $result;
    }

    /**
     * @param Player $player
     * @param BlockList $blocks
     */
    public function addRedo(Player $player, BlockList $blocks) {
        $this->redoData[$player->getName()][] = $blocks;
    }

    /**
     * @param Player $player
     */
    public function redo(Player $player) {
        if(empty($this->redoData[$player->getName()]) || count($this->redoData[$player->getName()]) == 0) {
            $player->sendMessage(BuilderTools::getPrefix()."§cThere are not actions to redo!");
            return;
        }

        if(count($this->redoData) == 1 && isset($this->redoData[$player->getName()][0])) {
            $blockList = $this->redoData[$player->getName()][0];
        }
        else {
            $blockList = end($this->redoData[$player->getName()]);
        }

        /** @var Filler $filler */
        $filler = BuilderTools::getEditor(static::FILLER);
        $result = $filler->fill($player, $blockList);

        array_pop($this->redoData[$player->getName()]);

    }
}