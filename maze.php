<?php

class Coord
{
    private int $x = 0;
    private int $y = 0;
    private int $w;
    private ?int $index = 0;

    public function __construct(int $width, int $height)
    {
        $this->w = $width;
        $this->h = $height;
    }

    public function x(): int
    {
        return $this->x;
    }

    public function y(): int
    {
        return $this->y;
    }

    public function index(): ?int
    {
        return $this->index;
    }

    public function up(): self
    {
        return $this->create($this->x, $this->y-1);
    }

    public function down(): self
    {
        return $this->create($this->x, $this->y+1);
    }

    public function left(): self
    {
        return $this->create($this->x-1, $this->y);
    }

    public function right(): self
    {
        return $this->create($this->x+1, $this->y);
    }

    public function isInside(): bool
    {
        return $this->index >= 0;
    }

    private function create(int $x, int $y): self
    {
        $c = new self($this->w, $this->h);
        $c->x = $x;
        $c->y = $y;
        $c->index;
        if ($x < 0 || $x >= $this->w) {
            $c->index = null;
        }elseif ($y < 0 || $y >= $this->h) {
            $c->index = null;
        } else {
            $c->index = $y * $this->w + $x;
        }

        return $c;
    }
}

class Grid
{
    private int $width;
    private int $height;
    private array $grid = [];

    private const EMPTY = 0;
    private const WALL = 1;
    private const VISITED = 2;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->grid = array_fill(0, $width * $height, self::WALL);

        for ($x = 1; $x < $width; $x += 2) {
            for ($y = 1; $y < $height; $y += 2) {
                $this->grid[$y*$width+$x] = self::EMPTY;
            }
        }
    }

    public function run() : void
    {
        $remaining = [(new Coord($this->width, $this->height))->right()->down()];
        while($remaining) {
            $current = array_pop($remaining);
            if($next = $this->visit($current)) {
                $remaining[] = $current;
                $remaining[] = $next;
            } else {
                shuffle($remaining);
            }
        }
    }

    public function display(): void
    {
        for($i=0, $size = count($this->grid);$i<$size; ++$i) {
            switch($this->grid[$i]) {
                case self::EMPTY: echo '.'; break;
                case self::WALL: echo  'X'; break;
                case self::VISITED: echo 'O'; break;
            }
            if(($i+1) % $this->width === 0) {
                echo PHP_EOL;
            }
        }
    }

    private function visit(Coord $coord): ?Coord
    {
        $index = $coord->index();
        if($index === null) {
            return null;
        }
        $this->grid[$index] = self::VISITED;

        $neighbors = [
            [$coord->up(), $coord->up()->up()],
            [$coord->right(), $coord->right()->right()],
            [$coord->down(), $coord->down()->down()],
            [$coord->left(), $coord->left()->left()],
        ];

        $neighbors = array_filter($neighbors, fn($n) => ($this->grid[$n[0]->index()]??null) === self::WALL && ($this->grid[$n[1]->index()]??null) === self::EMPTY);

        if(!$neighbors ) {
            return null;
        }
        $selected = $neighbors[array_rand($neighbors)];

        // Break the wall
        $this->grid[$selected[0]->index()] = self::VISITED;
        $this->grid[$selected[1]->index()] = self::VISITED;

        return $selected[1];
    }

    public function dumpWalls(int $wallValue): string
    {
        return join(',', array_map(
            fn($c) => $c === self::WALL ? $wallValue : 0,
            $this->grid
        ));
    }
}

$grid = new Grid(129, 129);
$grid->run();
//$grid->display();
echo $grid->dumpWalls(294) . PHP_EOL;
