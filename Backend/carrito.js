let cart = [];
function addToCart(product, price){
cart.push({product, price});
updateCart();
}

function updateCart(){
const cartItems = document.getElementById("cart-items");
cartItems.innerHTML = "";
let total = 0;
cart.forEach(item => {
const div = document.createElement("div");
div.innerHTML = item.product + " - $" + item.price;
cartItems.appendChild(div);
total += item.price;
});
document.querySelector(".total h3").textContent = "Total: $" + total;
}