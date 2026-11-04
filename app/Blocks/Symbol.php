<?php

namespace App\Blocks;

/*
 | The site's own icons, drawn with a line in the text's colour on a 24-unit
 | grid, for the blocks that show one beside their text: a card, a plugin, a
 | section of the documentation. A Select of them stores the name.
 */
enum Symbol: string
{
    case Arrow = 'arrow';
    case Lines = 'lines';
    case Nodes = 'nodes';
    case Window = 'window';
    case Pencil = 'pencil';
    case Plug = 'plug';
    case Star = 'star';
    case Form = 'form';
    case Search = 'search';
    case Cloud = 'cloud';
    case Pin = 'pin';
    case Code = 'code';
    case Video = 'video';
    case Palette = 'palette';
    case Log = 'log';
    case Drag = 'drag';
    case Move = 'move';
    case Image = 'image';
    case Settings = 'settings';
    case Duplicate = 'duplicate';
    case Delete = 'delete';
    case Key = 'key';
    case Spark = 'spark';

    public function path(): string
    {
        return match ($this) {
            self::Arrow => 'M5 12h14M13 6l6 6-6 6',
            self::Lines => 'M4 6h16M4 12h16M4 18h10',
            self::Nodes => 'M7 7h4v4H7zM13 13h4v4h-4zM11 9h4v4',
            self::Window => 'M4 5h16v14H4zM4 9h16',
            self::Pencil => 'M4 20h4L19 9l-4-4L4 16zM13 7l4 4',
            self::Plug => 'M9 3v4M15 3v4M7 7h10v5a5 5 0 01-10 0zM12 17v4',
            self::Star => 'M12 3l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.4 6.8 19.1l1-5.8L3.5 9.2l5.9-.9z',
            self::Form => 'M5 4h14v16H5zM9 9h6M9 13h6M9 17h3',
            self::Search => 'M11 4a7 7 0 100 14 7 7 0 000-14zM20 20l-4-4',
            self::Cloud => 'M4 14a4 4 0 014-4 6 6 0 0111.5 2A3.5 3.5 0 0118 19H8a4 4 0 01-4-5z',
            self::Pin => 'M12 21s-7-6.2-7-11a7 7 0 0114 0c0 4.8-7 11-7 11zM12 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z',
            self::Code => 'M8 7l-5 5 5 5M16 7l5 5-5 5',
            self::Video => 'M4 6h12v12H4zM16 10l4-2v8l-4-2',
            self::Palette => 'M12 3a9 9 0 100 18c1 0 1.5-.8 1.5-1.5 0-1.2-1-1.5-1-2.5s.8-1.5 2-1.5H17a4 4 0 004-4c0-4.4-4-8.5-9-8.5z',
            self::Log => 'M4 5h16M4 10h16M4 15h10M4 20h7',
            self::Drag => 'M9 5h.01M15 5h.01M9 12h.01M15 12h.01M9 19h.01M15 19h.01',
            self::Move => 'M12 5v14M6 11l6-6 6 6',
            self::Image => 'M3 5h18v14H3zM3 16l5-5 5 5 3-3 5 5',
            self::Settings => 'M4 7h10M18 7h2M4 17h4M12 17h8M14 5v4M8 15v4',
            self::Duplicate => 'M8 8h12v12H8zM4 16V4h12',
            self::Delete => 'M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
            self::Key => 'M14.5 4a5.5 5.5 0 11-4.9 8L4 17.6V20h3v-2h2v-2l1.6-1.6A5.5 5.5 0 0114.5 4zM16 8h.01',
            self::Spark => 'M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18',
        };
    }
}
