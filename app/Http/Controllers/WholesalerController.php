class WholesalerController extends Controller
{
    public function index()
    {
        // Incoming orders from retailers
        $incomingOrders = Order::where('seller_id', auth()->id())->with('buyer', 'items')->get();

        return view('wholesaler.dashboard', compact('incomingOrders'));
    }

    public function approveOrder(Order $order)
    {
        // Only allow wholesaler to approve orders directed to them
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'approved']);

        return redirect()->route('wholesaler.dashboard')->with('success', 'Order approved!');
    }

    public function createOrder()
    {
        $factories = User::role('factory')->get();
        $products = Product::whereIn('owner_id', $factories->pluck('id'))->get();

        return view('wholesaler.create-order', compact('factories', 'products'));
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($request) {
            $order = Order::create([
                'buyer_id' => auth()->id(),
                'seller_id' => $request->seller_id,
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            $order->update(['total_amount' => $total]);
        });

        return redirect()->route('wholesaler.dashboard')->with('success', 'Order sent to factory.');
    }
}

