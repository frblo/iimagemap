<?php

/**
 * DokuWiki Plugin iimagemap
 *
 * Syntax:
 * {{iimagemap>image_url|Title}}
 * [[link|icon.svg|Label @ x,y,size]]
 * ...
 * {{<iimagemap}}
 *
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_iimagemap extends DokuWiki_Syntax_Plugin {
    private $img = '';
    private $title = '';
    private $id = '';

    public function getType() { return 'container'; }
    public function getSort() { return 185; }
    public function getPType() { return 'block'; }

    public function connectTo($mode) {
        if ($mode == 'base') {
            $this->Lexer->addEntryPattern('\{\{iimagemap>[^}]+\}\}', $mode, 'plugin_iimagemap');
            $this->Lexer->addPattern('\[\[[^\]]+\]\]', 'plugin_iimagemap');
        }
    }

    public function postConnect() {
        $this->Lexer->addExitPattern('\{\{<iimagemap\}\}', 'plugin_iimagemap');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $inner = substr($match, strlen('{{iimagemap>'), -2);
                $parts = explode('|', $inner, 2);
                $this->img = trim($parts[0]);
                $this->title = isset($parts[1]) ? trim($parts[1]) : '';
                $this->id = 'iimap_' . mt_rand();
                return array('state' => $state);

            case DOKU_LEXER_MATCHED:
                $text = trim($match, '[] ');
                $parts = explode('@', $text);
                if (count($parts) !== 2) {
                    return array();
                }
                $info = trim($parts[0]);
                $coords = trim($parts[1]);
                $infoParts = explode('|', $info);
                if (count($infoParts) < 3) {
                    return array();
                }
                $link = trim($infoParts[0]);
                $icon = trim($infoParts[1]);
                $label = trim($infoParts[2]);
                $coordParts = array_map('trim', explode(',', $coords));
                if (count($coordParts) < 3) {
                    return array();
                }
                $x = intval($coordParts[0]);
                $y = intval($coordParts[1]);
                $size = intval($coordParts[2]);
                return array(
                    'state' => $state,
                    'link'  => $link,
                    'icon'  => $icon,
                    'label' => $label,
                    'x'     => $x,
                    'y'     => $y,
                    'size'  => $size
                );

            case DOKU_LEXER_EXIT:
                return array('state' => $state);

            default:
                return array();
        }
    }

    public function render($format, Doku_Renderer $renderer, $data) {
        if ($format !== 'xhtml') return false;
        $state = $data['state'];
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $renderer->doc .= '<div class="iimap-wrapper" style="position: relative; overflow: hidden; border: 1px solid #ccc;">';
                if ($this->title) {
                    $renderer->doc .= '<div class="iimap-title" style="text-align: center; margin-bottom: 5px; font-weight: bold;">' . htmlspecialchars($this->title) . '</div>';
                }
                $renderer->doc .= '<div id="' . $this->id . '" class="iimap-container" style="position: relative; display: inline-block;">';
                $renderer->doc .= '<img src="' . htmlspecialchars($this->img) . '" style="display: block; max-width: 100%; height: auto;" />';
                break;

            case DOKU_LEXER_MATCHED:
                $renderer->doc .= '<a href="' . htmlspecialchars($data['link']) . '" title="' . htmlspecialchars($data['label']) . '" ';
                $renderer->doc .= 'class="iimap-marker" ';
                $renderer->doc .= 'data-x="' . $data['x'] . '" data-y="' . $data['y'] . '" ';
                $renderer->doc .= 'style="position: absolute; left: ' . $data['x'] . 'px; top: ' . $data['y'] . 'px; width: ' . $data['size'] . 'px; transform: translate(-50%, -50%) scale(1);"';
                $renderer->doc .= '>';
                $renderer->doc .= '<img src="' . htmlspecialchars($data['icon']) . '" alt="' . htmlspecialchars($data['label']) . '" ';
                $renderer->doc .= 'style="width: 100%; height: 100%;" />';
                $renderer->doc .= '</a>';
                break;

            case DOKU_LEXER_EXIT:
                $renderer->doc .= '</div>';
                $renderer->doc .= '</div>';
                $renderer->doc .= '<script src="https://unpkg.com/@panzoom/panzoom/dist/panzoom.min.js"></script>';
                $renderer->doc .= '<script>';
                $renderer->doc .= "document.addEventListener('DOMContentLoaded', function() {";
                $renderer->doc .= "  var el = document.getElementById('" . $this->id . "');";
                $renderer->doc .= "  console.log('iimagemap: initializing panzoom for id=' + el.id);";
                $renderer->doc .= "  var panzoomInstance = Panzoom(el, {";
                $renderer->doc .= "    maxScale: 5,";
                $renderer->doc .= "    contain: 'outside'";
                $renderer->doc .= "  });";
                $renderer->doc .= "  console.log('iimagemap: panzoomInstance created');";
                $renderer->doc .= "  el.parentElement.addEventListener('wheel', panzoomInstance.zoomWithWheel);\n";
                $renderer->doc .= "  console.log('iimagemap: wheel event bound to', el.parentElement);";
                $renderer->doc .= "});";
                $renderer->doc .= '</script>';
                break;
        }
        return true;
    }
}
