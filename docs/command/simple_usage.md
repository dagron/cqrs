# Simple usage commands

Commands, in the [CQRS](https://martinfowler.com/bliki/CQRS.html) approach, are designed to change the data in the
application.

For example, consider the procedure for renaming an article.

Create a command to rename:

```php
use GpsLab\Component\Command\Command;

class RenameArticleCommand implements Command
{
    /**
     * @var int
     */
    public $article_id;

    /**
     * @var string
     */
    public $new_name = '';
}
```

You can use private properties to better control the types of data and required properties:

```php
use GpsLab\Component\Command\Command;

class RenameArticleCommand implements Command
{
    private $article_id;

    private $new_name = '';

    public function __construct(integer $article_id, string $new_name)
    {
        $this->article_id = $article_id;
        $this->new_name = $new_name;
    }

    public function articleId()
    {
        return $this->article_id;
    }

    public function newName()
    {
        return $this->new_name;
    }
}
```

> **Note**
>
> To simplify the filling of the team, you can use [payload](https://github.com/gpslab/payload).

Now create a command handler. For example we use [Doctrine ORM](https://github.com/doctrine/doctrine2).

```php
use GpsLab\Component\Command\Command;
use GpsLab\Component\Command\Handler\CommandHandler;
use Doctrine\ORM\EntityManagerInterface;

class RenameArticleHandler implements CommandHandler
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handle(Command $command)
    {
        // you need to make sure that this is the team that we expect
        if ($command instanceof RenameArticleCommand) {
            // get article by id
            $article = $this->em->getRepository(Article::class)->find($command->article_id);
            $article->rename($command->new_name);
        }
    }
}
```

To not check the type of command, you can use the switch:

```php
use GpsLab\Component\Command\Command;
use GpsLab\Component\Command\Handler\SwitchCommandHandler;
use Doctrine\ORM\EntityManagerInterface;

class RenameArticleHandler extends SwitchCommandHandler
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function handleRenameArticle(RenameArticleCommand $command)
    {
        $article = $this->em->getRepository(Article::class)->find($command->article_id);
        $article->rename($command->new_name);
    }
}
```

And now we register handler and handle command.

```php
use GpsLab\Component\Command\Bus\HandlerLocatedCommandBus;
use GpsLab\Component\Command\Handler\Locator\DirectBindingCommandHandlerLocator;

// register command handler in handler locator
$locator = new DirectBindingCommandHandlerLocator();
$locator->registerHandler(RenameArticleCommand::class, new RenameArticleHandler($em));

// create bus with command handler locator
$bus = new HandlerLocatedCommandBus($locator);

// ...

// create rename article command
$command = new RenameArticleCommand();
$command->article_id = $article_id;
$command->new_name = $new_name;

// handle command
$bus->handle($command);
```

> **Note**
>
> To monitor the execution of commands, you can use [middleware](https://github.com/gpslab/middleware).