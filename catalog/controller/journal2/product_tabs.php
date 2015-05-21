<?php
class ControllerJournal2ProductTabs extends Controller {

    public function index() {
        if ($this->journal2->page->getType() !== 'product') return;

        $product_id = $this->journal2->page->getId();
        $this->load->model('journal2/module');

        $tabs = $this->model_journal2_module->getProductTabs($product_id);
        $tabs = Journal2Utils::sortArray($tabs);

        $tab_tab = array();
        $tab_desc_top = array();
        $tab_desc_bottom = array();
        $tab_image = array();
        foreach ($tabs as $tab) {
            if (!$tab['status']) continue;

            $css = array();

            if (Journal2Utils::getColor(Journal2Utils::getProperty($tab, 'icon_bg_color.value.color'))) {
                $css[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($tab, 'icon_bg_color.value.color'));
            }
            if (Journal2Utils::getProperty($tab, 'icon_width')) {
                $css[] = 'width: ' . Journal2Utils::getProperty($tab, 'icon_width') . 'px';
            }
            if (Journal2Utils::getProperty($tab, 'icon_height')) {
                $css[] = 'height: ' . Journal2Utils::getProperty($tab, 'icon_height') . 'px';
                $css[] = 'line-height: ' . Journal2Utils::getProperty($tab, 'icon_height') . 'px';
            }
            if (Journal2Utils::getProperty($tab, 'icon_border')) {
                $css = array_merge($css, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($tab, 'icon_border')));
            }

            $data = array(
                'name'      => Journal2Utils::getProperty($tab, 'name.value.' . $this->config->get('config_language_id')),
                'has_icon' => Journal2Utils::getProperty($tab, 'icon_status'),
                'icon_position' => Journal2Utils::getProperty($tab, 'icon_position', 'top'),
                'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($tab, 'icon')),
                'icon_css' => implode('; ', $css),
                'content'   => Journal2Utils::getProperty($tab, 'content.' . $this->config->get('config_language_id'))
            );
            switch (Journal2Utils::getProperty($tab, 'position')) {
                case 'tab':
                    $tab_tab[] = $data;
                    break;
                case 'desc':
                    if (Journal2Utils::getProperty($tab, 'option_position') === 'top') {
                        $tab_desc_top[] = $data;
                    } else {
                        $tab_desc_bottom[] = $data;
                    }
                    break;
                case 'image':
                    $tab_image[] = $data;
                    break;
            }
        }
        $this->journal2->settings->set('additional_product_tabs', $tab_tab);
        $this->journal2->settings->set('additional_product_description_top', $tab_desc_top);
        $this->journal2->settings->set('additional_product_description_bottom', $tab_desc_bottom);
        $this->journal2->settings->set('additional_product_description_image', $tab_image);
    }

}
?>