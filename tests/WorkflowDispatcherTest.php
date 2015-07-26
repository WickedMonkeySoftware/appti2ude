<?

require_once 'src/inter/ICommand.php';
require_once 'src/inter/IDispatch.php';
require_once 'src/inter/IEventStore.php';
require_once 'src/inter/IEvent.php';
require_once 'src/inter/ISnapshot.php';
require_once 'src/MagicClass.php';
require_once 'src/Aggregate.php';
require_once 'src/Event.php';
require_once 'src/Command.php';
require_once 'src/Snapshot.php';
require_once 'src/MemoryEventStore.php';
require_once 'src/MemoryDispatcher.php';

$store = new \appti2ude\MemoryEventStore();
$dispatch = new appti2ude\MemoryDispatcher($store);
$workflow = new appti2ude\WorkflowDispatcher($dispatch);

class PressButton extends \appti2ude\Command {
    protected function PressButtonInitialize() {
        $this->AddProperty('button', 'clr');
    }
}

class NumberPushed extends \appti2ude\Event {
    protected function NumberPushedInitialize() {
        $this->AddProperty('value', 0);
    }
}

class ClrPushed extends \appti2ude\Event {
    protected function ClrPushedInitialize() {
        $this->AddProperty('pushed', true);
    }
}

class OperatorPushed extends \appti2ude\Event {
    protected function OperatorPushedInitialize() {
        $this->AddProperty('operator', '=');
    }
}

class Calculator extends \appti2ude\Aggregate {
    protected function CalculatorInitialize() {
        $this->AddProperty('value', 0);
        $this->AddProperty('display', 0);
        $this->AddProperty('holdOp', '');
        $this->AddCommandHandler('PressButton', 'PressButton');
        $this->AddEventHandler('NumberPushed', 'NumberPushed');
        $this->AddEventHandler('ClrPushed', 'ClrPushed');
        $this->AddEventHandler('OperatorPushed', 'OperatorPushed');
    }

    function PressButton($command) {
        if (preg_match('/^[0-9]$/', $command->button)) {
            yield new NumberPushed($this->id, ['value' => $command->button]);
        }

        if (preg_match('/^(\\+|-|=)$/', $command->button)) {
            yield new OperatorPushed($this->id, ['operator' => $command->button]);
        }

        if ($command->button == 'clr') {
            yield new ClrPushed($this->id);
        }
    }

    function NumberPushed($event) {
        $number = $event->value;
        switch ($this->holdOp) {
            case '':
                $this->value = $number;
                break;
            case '+':
                $this->value += $number;
                break;
            case '-':
                $this->value -= $number;
                break;
            case '=':
                $this->value = $number;
        }

        $this->display .= " $number";
    }

    function ClrPushed($event) {
        $this->holdOp = '';
        $this->value = 0;
        $this->display = 0;
    }

    function OperatorPushed($event) {
        $op = $event->operator;
        switch ($op) {
            case '=':
                $this->display .= " = this->value";
                // fallthrough intentional
            default:
                $this->holdOp = $op;
                break;
        }
    }
}

$forscan = new Calculator();
$workflow->Scan($forscan);

class WorkflowDispatcherTest extends PHPUnit_Framework_TestCase {
    function testSimple() {
        global $dispatch;
        $dispatch->SendCommand(new PressButton('1', ['button' => '5']));
        $dispatch->SendCommand(new PressButton('1', ['button' => '+']));
        $dispatch->SendCommand(new PressButton('1', ['button' => '5']));
        $dispatch->SendCommand(new PressButton('1', ['button' => '=']));

        $result = new Calculator(null, '1');
        $snapshot = \appti2ude\Snapshot::CreateFromStore($dispatch, '1');
        $result->HydrateFromSnapshot($snapshot);

        $this->assertEquals('5 + 5 = 10', $result->display);
    }

    function testOutOfOrder() {
        global $dispatch;
        $dispatch->SendCommand(new PressButton('1', ['button' => '5']));
        $dispatch->SendCommand(new PressButton('1', ['button' => '+']));
        $dispatch->SendCommand(new PressButton('1', ['button' => '+']));
        $dispatch->SendCommand(new PressButton('1', ['button' => '5']));
        $dispatch->SendCommand(new PressButton('1', ['button' => '5']));
        $dispatch->SendCommand(new PressButton('1', ['button' => '=']));

        $result = new Calculator(null, '1');
        $snapshot = \appti2ude\Snapshot::CreateFromStore($dispatch, '1');
        $result->HydrateFromSnapshot($snapshot);

        $this->assertEquals('5 + 5 + 55 = 65', $result->display);
    }
}
