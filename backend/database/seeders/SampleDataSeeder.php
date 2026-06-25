<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Combo;
use App\Models\ComboItem;
use App\Models\FloorPlan;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\ModifierOption;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Appetizers', 'slug' => 'appetizers', 'description' => 'Starters and small plates', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Main Course', 'slug' => 'main-course', 'description' => 'Hearty main dishes', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Desserts', 'slug' => 'desserts', 'description' => 'Sweet treats', 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Beverages', 'slug' => 'beverages', 'description' => 'Drinks and refreshments', 'sort_order' => 4, 'is_active' => true],
            ['name' => 'Sides', 'slug' => 'sides', 'description' => 'Extra sides and add-ons', 'sort_order' => 5, 'is_active' => true],
        ]);
        $categories->each(fn ($data) => Category::create($data));

        $menuItems = collect([
            ['category_id' => 1, 'name' => 'Spring Rolls', 'slug' => 'spring-rolls', 'description' => 'Crispy vegetable spring rolls', 'price' => 6.99, 'cost' => 2.50, 'is_active' => true, 'is_available' => true],
            ['category_id' => 1, 'name' => 'Chicken Wings', 'slug' => 'chicken-wings', 'description' => 'Buffalo style with ranch dip', 'price' => 10.99, 'cost' => 4.00, 'is_active' => true, 'is_available' => true],
            ['category_id' => 1, 'name' => 'Bruschetta', 'slug' => 'bruschetta', 'description' => 'Toasted bread with tomato basil topping', 'price' => 8.49, 'cost' => 3.00, 'is_active' => true, 'is_available' => true],
            ['category_id' => 2, 'name' => 'Grilled Salmon', 'slug' => 'grilled-salmon', 'description' => 'Atlantic salmon with lemon butter sauce', 'price' => 18.99, 'cost' => 8.00, 'is_active' => true, 'is_available' => true],
            ['category_id' => 2, 'name' => 'Beef Steak', 'slug' => 'beef-steak', 'description' => '8oz ribeye with mashed potatoes', 'price' => 24.99, 'cost' => 11.00, 'is_active' => true, 'is_available' => true],
            ['category_id' => 2, 'name' => 'Chicken Pasta', 'slug' => 'chicken-pasta', 'description' => 'Creamy alfredo with grilled chicken', 'price' => 14.99, 'cost' => 5.50, 'is_active' => true, 'is_available' => true],
            ['category_id' => 2, 'name' => 'Veggie Pizza', 'slug' => 'veggie-pizza', 'description' => 'Margherita with fresh basil', 'price' => 12.99, 'cost' => 4.50, 'is_active' => true, 'is_available' => true],
            ['category_id' => 3, 'name' => 'Chocolate Lava Cake', 'slug' => 'chocolate-lava-cake', 'description' => 'Warm chocolate cake with ice cream', 'price' => 7.99, 'cost' => 2.50, 'is_active' => true, 'is_available' => true],
            ['category_id' => 3, 'name' => 'Cheesecake', 'slug' => 'cheesecake', 'description' => 'New York style with berry compote', 'price' => 6.99, 'cost' => 2.00, 'is_active' => true, 'is_available' => true],
            ['category_id' => 3, 'name' => 'Ice Cream Sundae', 'slug' => 'ice-cream-sundae', 'description' => 'Vanilla with chocolate sauce and nuts', 'price' => 5.49, 'cost' => 1.50, 'is_active' => true, 'is_available' => true],
            ['category_id' => 4, 'name' => 'Coffee', 'slug' => 'coffee', 'description' => 'Fresh brewed coffee', 'price' => 2.99, 'cost' => 0.50, 'is_active' => true, 'is_available' => true],
            ['category_id' => 4, 'name' => 'Iced Tea', 'slug' => 'iced-tea', 'description' => 'House-made lemon iced tea', 'price' => 2.49, 'cost' => 0.30, 'is_active' => true, 'is_available' => true],
            ['category_id' => 4, 'name' => 'Orange Juice', 'slug' => 'orange-juice', 'description' => 'Fresh squeezed', 'price' => 3.99, 'cost' => 1.00, 'is_active' => true, 'is_available' => true],
            ['category_id' => 5, 'name' => 'French Fries', 'slug' => 'french-fries', 'description' => 'Crispy seasoned fries', 'price' => 3.99, 'cost' => 1.00, 'is_active' => true, 'is_available' => true],
            ['category_id' => 5, 'name' => 'Garden Salad', 'slug' => 'garden-salad', 'description' => 'Mixed greens with vinaigrette', 'price' => 4.99, 'cost' => 1.50, 'is_active' => true, 'is_available' => true],
        ]);
        $menuItems->each(fn ($data) => MenuItem::create($data));

        $modifiers = collect([
            ['name' => 'Doneness', 'type' => 'single', 'is_required' => true, 'min_selection' => 1, 'max_selection' => 1],
            ['name' => 'Extra Toppings', 'type' => 'multi', 'is_required' => false, 'min_selection' => 0, 'max_selection' => 3],
            ['name' => 'Drink Size', 'type' => 'single', 'is_required' => true, 'min_selection' => 1, 'max_selection' => 1],
            ['name' => 'Dressing', 'type' => 'single', 'is_required' => true, 'min_selection' => 1, 'max_selection' => 1],
        ]);
        $modifiers->each(fn ($data) => Modifier::create($data));

        $modifierOptions = collect([
            ['modifier_id' => 1, 'name' => 'Rare', 'price_adjustment' => 0, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 1, 'name' => 'Medium Rare', 'price_adjustment' => 0, 'is_default' => true, 'is_active' => true],
            ['modifier_id' => 1, 'name' => 'Medium', 'price_adjustment' => 0, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 1, 'name' => 'Well Done', 'price_adjustment' => 0, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 2, 'name' => 'Extra Cheese', 'price_adjustment' => 1.50, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 2, 'name' => 'Bacon Bits', 'price_adjustment' => 2.00, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 2, 'name' => 'Avocado', 'price_adjustment' => 2.50, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 2, 'name' => 'Mushrooms', 'price_adjustment' => 1.50, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 3, 'name' => 'Small', 'price_adjustment' => 0, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 3, 'name' => 'Medium', 'price_adjustment' => 0.50, 'is_default' => true, 'is_active' => true],
            ['modifier_id' => 3, 'name' => 'Large', 'price_adjustment' => 1.00, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 4, 'name' => 'Caesar', 'price_adjustment' => 0, 'is_default' => true, 'is_active' => true],
            ['modifier_id' => 4, 'name' => 'Ranch', 'price_adjustment' => 0, 'is_default' => false, 'is_active' => true],
            ['modifier_id' => 4, 'name' => 'Italian', 'price_adjustment' => 0, 'is_default' => false, 'is_active' => true],
        ]);
        $modifierOptions->each(fn ($data) => ModifierOption::create($data));

        $combo = Combo::create([
            'name' => 'Lunch Special',
            'description' => 'Main course + beverage + dessert',
            'price' => 19.99,
            'is_active' => true,
        ]);
        ComboItem::create(['combo_id' => $combo->id, 'menu_item_id' => 6, 'quantity' => 1]);
        ComboItem::create(['combo_id' => $combo->id, 'menu_item_id' => 11, 'quantity' => 1]);
        ComboItem::create(['combo_id' => $combo->id, 'menu_item_id' => 9, 'quantity' => 1]);

        $floorPlan = FloorPlan::create([
            'name' => 'Main Dining Hall',
            'width' => 800,
            'height' => 600,
            'is_active' => true,
        ]);

        $tables = collect([
            ['table_number' => 1, 'capacity' => 2, 'section' => 'Window', 'status' => 'free', 'pos_x' => 50, 'pos_y' => 50, 'width' => 80, 'height' => 80, 'shape' => 'rectangle', 'floor_plan_id' => $floorPlan->id],
            ['table_number' => 2, 'capacity' => 2, 'section' => 'Window', 'status' => 'occupied', 'pos_x' => 200, 'pos_y' => 50, 'width' => 80, 'height' => 80, 'shape' => 'rectangle', 'floor_plan_id' => $floorPlan->id],
            ['table_number' => 3, 'capacity' => 4, 'section' => 'Center', 'status' => 'free', 'pos_x' => 350, 'pos_y' => 100, 'width' => 120, 'height' => 120, 'shape' => 'rectangle', 'floor_plan_id' => $floorPlan->id],
            ['table_number' => 4, 'capacity' => 4, 'section' => 'Center', 'status' => 'reserved', 'pos_x' => 500, 'pos_y' => 100, 'width' => 120, 'height' => 120, 'shape' => 'rectangle', 'floor_plan_id' => $floorPlan->id],
            ['table_number' => 5, 'capacity' => 6, 'section' => 'Patio', 'status' => 'free', 'pos_x' => 100, 'pos_y' => 300, 'width' => 140, 'height' => 140, 'shape' => 'rectangle', 'floor_plan_id' => $floorPlan->id],
            ['table_number' => 6, 'capacity' => 4, 'section' => 'Patio', 'status' => 'dirty', 'pos_x' => 300, 'pos_y' => 350, 'width' => 120, 'height' => 120, 'shape' => 'rectangle', 'floor_plan_id' => $floorPlan->id],
            ['table_number' => 7, 'capacity' => 2, 'section' => 'Bar', 'status' => 'occupied', 'pos_x' => 550, 'pos_y' => 300, 'width' => 80, 'height' => 80, 'shape' => 'circle', 'floor_plan_id' => $floorPlan->id],
            ['table_number' => 8, 'capacity' => 2, 'section' => 'Bar', 'status' => 'occupied', 'pos_x' => 550, 'pos_y' => 420, 'width' => 80, 'height' => 80, 'shape' => 'circle', 'floor_plan_id' => $floorPlan->id],
        ]);
        $tables->each(fn ($data) => RestaurantTable::create($data));
    }
}
