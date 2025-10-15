<?php
/**
 * Power Reservations Elementor Widget
 *
 * @package PowerReservations
 * @since 1.0.0
 * @license GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check if Elementor is loaded
if (!did_action('elementor/loaded')) {
    return;
}

// Check if base widget class exists
if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

/**
 * Elementor Power Reservations Widget
 */
class PowerReservations_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'power_reservations';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __('Power Reservations Form', 'power-reservations');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['power-reservations'];
    }

    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['reservation', 'booking', 'form', 'restaurant', 'power'];
    }

    /**
     * Register widget controls
     */
    protected function _register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'power-reservations'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_title',
            [
                'label' => __('Form Title', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Make a Reservation', 'power-reservations'),
                'placeholder' => __('Enter form title', 'power-reservations'),
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'power-reservations'),
                'label_off' => __('Hide', 'power-reservations'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'form_style',
            [
                'label' => __('Form Style', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => __('Default', 'power-reservations'),
                    'minimal' => __('Minimal', 'power-reservations'),
                    'modern' => __('Modern', 'power-reservations'),
                    'elegant' => __('Elegant', 'power-reservations'),
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Form Container
        $this->start_controls_section(
            'form_container_style',
            [
                'label' => __('Form Container', 'power-reservations'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_background_color',
            [
                'label' => __('Background Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-form' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'form_border',
                'label' => __('Border', 'power-reservations'),
                'selector' => '{{WRAPPER}} .pr-elementor-form',
            ]
        );

        $this->add_responsive_control(
            'form_border_radius',
            [
                'label' => __('Border Radius', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_padding',
            [
                'label' => __('Padding', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'form_box_shadow',
                'label' => __('Box Shadow', 'power-reservations'),
                'selector' => '{{WRAPPER}} .pr-elementor-form',
            ]
        );

        $this->end_controls_section();

        // Style Section - Form Title
        $this->start_controls_section(
            'title_style',
            [
                'label' => __('Title Style', 'power-reservations'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'power-reservations'),
                'selector' => '{{WRAPPER}} .pr-elementor-title',
            ]
        );

        $this->add_responsive_control(
            'title_alignment',
            [
                'label' => __('Alignment', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'power-reservations'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'power-reservations'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'power-reservations'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margin', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Form Fields
        $this->start_controls_section(
            'fields_style',
            [
                'label' => __('Form Fields', 'power-reservations'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => __('Label Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-form label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'label' => __('Label Typography', 'power-reservations'),
                'selector' => '{{WRAPPER}} .pr-elementor-form label',
            ]
        );

        $this->add_control(
            'field_background_color',
            [
                'label' => __('Field Background', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-form input[type="text"]' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pr-elementor-form input[type="email"]' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pr-elementor-form input[type="tel"]' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pr-elementor-form input[type="date"]' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pr-elementor-form select' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pr-elementor-form textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'field_text_color',
            [
                'label' => __('Field Text Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-form input' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .pr-elementor-form select' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .pr-elementor-form textarea' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'field_border',
                'label' => __('Field Border', 'power-reservations'),
                'selector' => '{{WRAPPER}} .pr-elementor-form input, {{WRAPPER}} .pr-elementor-form select, {{WRAPPER}} .pr-elementor-form textarea',
            ]
        );

        $this->add_responsive_control(
            'field_border_radius',
            [
                'label' => __('Field Border Radius', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-form input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .pr-elementor-form select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .pr-elementor-form textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'field_padding',
            [
                'label' => __('Field Padding', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-form input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .pr-elementor-form select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .pr-elementor-form textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Submit Button
        $this->start_controls_section(
            'button_style',
            [
                'label' => __('Submit Button', 'power-reservations'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'power-reservations'),
                'selector' => '{{WRAPPER}} .pr-elementor-submit',
            ]
        );

        $this->start_controls_tabs('button_style_tabs');

        // Normal State
        $this->start_controls_tab(
            'button_normal',
            [
                'label' => __('Normal', 'power-reservations'),
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => __('Text Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-submit' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label' => __('Background Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-submit' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'label' => __('Border', 'power-reservations'),
                'selector' => '{{WRAPPER}} .pr-elementor-submit',
            ]
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'button_hover',
            [
                'label' => __('Hover', 'power-reservations'),
            ]
        );

        $this->add_control(
            'button_hover_color',
            [
                'label' => __('Text Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-submit:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background',
            [
                'label' => __('Background Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-submit:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __('Border Color', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-submit:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_margin',
            [
                'label' => __('Margin', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-submit' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_alignment',
            [
                'label' => __('Alignment', 'power-reservations'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'power-reservations'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'power-reservations'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'power-reservations'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'power-reservations'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .pr-elementor-button-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Get form configuration
        $form_style = $settings['form_style'];
        $show_title = $settings['show_title'] === 'yes';
        $form_title = $settings['form_title'];

        // Start output
        echo '<div class="pr-elementor-widget pr-elementor-style-' . esc_attr($form_style) . '">';
        
        if ($show_title && !empty($form_title)) {
            echo '<h3 class="pr-elementor-title">' . esc_html($form_title) . '</h3>';
        }

        echo '<div class="pr-elementor-form">';
        
        // Render the reservation form
        $this->render_reservation_form($form_style);
        
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render reservation form with Elementor styling
     */
    private function render_reservation_form($style = 'default') {
        // Get plugin settings
        $time_slots = get_option('pr_time_slots', array(
            '6:00 PM', '6:30 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM', '9:00 PM', '9:30 PM'
        ));
        $max_party_size = get_option('pr_max_party_size', 8);
        $booking_window = get_option('pr_booking_window', 30);
        
        // Get field configuration
        $available_fields = array(
            'name' => array(
                'label' => __('Name', 'power-reservations'),
                'type' => 'text',
                'required_default' => true
            ),
            'email' => array(
                'label' => __('Email', 'power-reservations'),
                'type' => 'email',
                'required_default' => true
            ),
            'phone' => array(
                'label' => __('Phone', 'power-reservations'),
                'type' => 'tel',
                'required_default' => false
            ),
            'date' => array(
                'label' => __('Date', 'power-reservations'),
                'type' => 'date',
                'required_default' => true
            ),
            'time' => array(
                'label' => __('Time', 'power-reservations'),
                'type' => 'select',
                'required_default' => true
            ),
            'party_size' => array(
                'label' => __('Party Size', 'power-reservations'),
                'type' => 'select',
                'required_default' => true
            ),
            'special_requests' => array(
                'label' => __('Special Requests', 'power-reservations'),
                'type' => 'textarea',
                'required_default' => false
            )
        );
        
        $current_fields = get_option('pr_form_fields', array_keys($available_fields));
        $field_order = get_option('pr_form_field_order', array_keys($available_fields));
        $field_settings = get_option('pr_form_field_settings', array());
        
        $min_date = date('Y-m-d', strtotime('+1 day'));
        $max_date = date('Y-m-d', strtotime("+{$booking_window} days"));
        
        echo '<form id="pr-elementor-form" class="pr-form pr-elementor-form-' . esc_attr($style) . '">';
        wp_nonce_field('pr_nonce', 'pr_nonce');
        
        // Render fields based on configuration
        foreach ($field_order as $field_key) {
            if (!in_array($field_key, $current_fields) || !isset($available_fields[$field_key])) {
                continue; // Skip disabled or invalid fields
            }
            
            $field_info = $available_fields[$field_key];
            $is_required = isset($field_settings[$field_key]['required']) ? $field_settings[$field_key]['required'] : $field_info['required_default'];
            $custom_label = isset($field_settings[$field_key]['label']) ? $field_settings[$field_key]['label'] : $field_info['label'];
            $placeholder = isset($field_settings[$field_key]['placeholder']) ? $field_settings[$field_key]['placeholder'] : '';
            
            $required_attr = $is_required ? 'required' : '';
            $required_text = $is_required ? ' *' : '';
            
            echo '<div class="pr-form-row pr-elementor-field">';
            echo '<label for="pr-elementor-' . $field_key . '">' . esc_html($custom_label) . $required_text . '</label>';
            
            switch ($field_key) {
                case 'time':
                    echo '<select id="pr-elementor-time" name="time" ' . $required_attr . '>';
                    echo '<option value="">' . __('Select a time', 'power-reservations') . '</option>';
                    foreach ($time_slots as $slot) {
                        echo '<option value="' . esc_attr($slot) . '">' . esc_html($slot) . '</option>';
                    }
                    echo '</select>';
                    break;
                    
                case 'party_size':
                    echo '<select id="pr-elementor-party-size" name="party_size" ' . $required_attr . '>';
                    echo '<option value="">' . __('Select party size', 'power-reservations') . '</option>';
                    for ($i = 1; $i <= $max_party_size; $i++) {
                        echo '<option value="' . $i . '">' . sprintf(_n('%d person', '%d people', $i, 'power-reservations'), $i) . '</option>';
                    }
                    echo '</select>';
                    break;
                    
                case 'date':
                    echo '<input type="date" id="pr-elementor-date" name="date" min="' . $min_date . '" max="' . $max_date . '" ' . $required_attr . ' placeholder="' . esc_attr($placeholder) . '">';
                    break;
                    
                case 'special_requests':
                    echo '<textarea id="pr-elementor-special-requests" name="special_requests" rows="3" placeholder="' . esc_attr($placeholder ?: __('Any special requests or dietary restrictions...', 'power-reservations')) . '"></textarea>';
                    break;
                    
                default:
                    echo '<input type="' . esc_attr($field_info['type']) . '" id="pr-elementor-' . $field_key . '" name="' . $field_key . '" ' . $required_attr . ' placeholder="' . esc_attr($placeholder) . '">';
                    break;
            }
            
            echo '</div>';
        }
        
        echo '<div class="pr-elementor-button-wrapper">';
        echo '<button type="submit" class="pr-elementor-submit">' . __('Make Reservation', 'power-reservations') . '</button>';
        echo '</div>';
        
        echo '<div id="pr-elementor-message" class="pr-message" style="display: none;"></div>';
        echo '</form>';
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <# 
        var form_style = settings.form_style;
        var show_title = settings.show_title === 'yes';
        var form_title = settings.form_title;
        #>
        
        <div class="pr-elementor-widget pr-elementor-style-{{{ form_style }}}">
            <# if (show_title && form_title) { #>
                <h3 class="pr-elementor-title">{{{ form_title }}}</h3>
            <# } #>
            
            <div class="pr-elementor-form">
                <form class="pr-form pr-elementor-form-{{{ form_style }}}">
                    <div class="pr-form-row pr-elementor-field">
                        <label><?php _e('Name *', 'power-reservations'); ?></label>
                        <input type="text" placeholder="<?php _e('Enter your name', 'power-reservations'); ?>">
                    </div>
                    
                    <div class="pr-form-row pr-elementor-field">
                        <label><?php _e('Email *', 'power-reservations'); ?></label>
                        <input type="email" placeholder="<?php _e('Enter your email', 'power-reservations'); ?>">
                    </div>
                    
                    <div class="pr-form-row pr-elementor-field">
                        <label><?php _e('Date *', 'power-reservations'); ?></label>
                        <input type="date">
                    </div>
                    
                    <div class="pr-form-row pr-elementor-field">
                        <label><?php _e('Time *', 'power-reservations'); ?></label>
                        <select>
                            <option><?php _e('Select a time', 'power-reservations'); ?></option>
                            <option>6:00 PM</option>
                            <option>7:00 PM</option>
                            <option>8:00 PM</option>
                        </select>
                    </div>
                    
                    <div class="pr-form-row pr-elementor-field">
                        <label><?php _e('Party Size *', 'power-reservations'); ?></label>
                        <select>
                            <option><?php _e('Select party size', 'power-reservations'); ?></option>
                            <option>2 people</option>
                            <option>4 people</option>
                            <option>6 people</option>
                        </select>
                    </div>
                    
                    <div class="pr-elementor-button-wrapper">
                        <button type="button" class="pr-elementor-submit"><?php _e('Make Reservation', 'power-reservations'); ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}