<?php
namespace CoinFlip;

use CoinFlip\pmforms\CustomForm;
use CoinFlip\pmforms\CustomFormResponse;
use CoinFlip\pmforms\element\Input;
use CoinFlip\pmforms\element\Label;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase
{
    public function onEnable(): void{
        $this->getLogger()->info(TextFormat::GREEN . "has successfully enabled.");
    }

    public function onDisable(): void{
        $this->getLogger()->info(TextFormat::RED . 'has successfully disabled.');
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
            switch ($command->getName()) {
                case 'coinflip':
                    $form = new CustomForm(TextFormat::DARK_AQUA . TextFormat::BOLD . 'COIN FLIP', [new Label('Response', TextFormat::YELLOW . 'Type in the amount of currency you want to bet below and then click the Submit button to flip the coin.'), new Input('Coin', 'Bet Amount:')], function (Player $Player, CustomFormResponse $response): void {
                        $amount = (int)$response->getString('Coin');
                        $form3 = new CustomForm(TextFormat::RED . TextFormat::BOLD . 'TOO BROKE', [new Label('Response', TextFormat::RED . 'You do not have $' . $amount . ' to coin flip.')], function (Player $Player, CustomFormResponse $response) use ($amount): void {
                        });
                        $form4 = new CustomForm(TextFormat::RED . TextFormat::BOLD . 'INVALID BET', [new Label('Response', TextFormat::RED . 'You cannot bet $' . $amount . ' in a coin flip.')], function (Player $Player, CustomFormResponse $response) use ($amount): void {
                        });
                        if ($amount <= 0) {
                            $Player->sendForm($form4);
                            return;
                        }
                        if (EconomyAPI::getInstance()->myMoney($Player) <= $amount) {
                            $Player->sendForm($form3);
                        }
                        if ($amount != 0) {
                            if (EconomyAPI::getInstance()->myMoney($Player) >= $amount) {
                                $chance = mt_rand(0, 100);
                                $form1 = new CustomForm(TextFormat::GREEN . TextFormat::BOLD . 'WINNER', [new Label('Response', TextFormat::GREEN . 'You won $' . $amount * 2 . ' in a coin flip.')], function (Player $Player) use ($amount): void {
                                    $Player->sendMessage(TextFormat::GREEN . "You won!\n + $" . $amount * 2);
                                });
                                $form2 = new CustomForm(TextFormat::RED . TextFormat::BOLD . 'LOOSER', [new Label('Response', TextFormat::RED . 'You lost $' . $amount . ' in a coin flip.')], function (Player $Player) use ($amount): void {
                                    $Player->sendMessage(TextFormat::RED . "You Lost!\n - $" . $amount);
                                });
                                if ($chance <= 50) {
                                    EconomyAPI::getInstance()->addMoney($Player, $amount);
                                    $Player->sendForm($form1);
                                } else {
                                    EconomyAPI::getInstance()->reduceMoney($Player, $amount);
                                    $Player->sendForm($form2);
                                }
                            }
                        }
                    });
                    $sender->sendForm($form);
            }
        }
        else{
            $this->getLogger()->alert(TextFormat::RED . 'This command is only use-able in-game.');
        }
        return true;
    }
}
