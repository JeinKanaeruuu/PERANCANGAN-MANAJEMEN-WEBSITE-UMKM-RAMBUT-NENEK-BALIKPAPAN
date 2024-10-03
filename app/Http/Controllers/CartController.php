<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::where('user_id', Auth::id())->with('product')->get();
        return view('cart.index', compact('carts'));
    }

    public function add(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        if ($product->stock < 1) {
            return redirect()->back()->with('error', 'Produk sedang kosong.');
        }

        $cart = Cart::firstOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $productId],
            ['quantity' => 1]
        );

        if (!$cart->wasRecentlyCreated) {
            $cart->quantity += 1;
            $cart->save();
        }

        return redirect()->back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, $cartId)
    {
        $cart = Cart::where('user_id', Auth::id())->findOrFail($cartId);
        $quantity = $request->input('quantity');

        if ($quantity < 1) {
            $cart->delete();
            return redirect()->back()->with('success', 'Produk dihapus dari keranjang.');
        }

        $product = $cart->product;
        if ($product->stock < $quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi.');
        }

        $cart->quantity = $quantity;
        $cart->save();

        return redirect()->back()->with('success', 'Keranjang diperbarui.');
    }

    public function remove($cartId)
    {
        $cart = Cart::where('user_id', Auth::id())->findOrFail($cartId);
        $cart->delete();

        return redirect()->back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
