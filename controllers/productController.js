const productModel = require('../models/productModel');

// GET ambil semua product
async function index(req, res) {
    try{
        const product = await productModel.getAllProducts();
        res.json({
            message: "Berhasil mengambil data produk",
            data: product
        });
    } catch (error) {
        res.status(500).json({
            message: "Gagal mengambil data produk",
            error: error.message
        })       
    }
}
// GET ambil product berdasarkan id
async function getById(req, res) {
    try {
        const product = await productModel.getProductById(req.params.id);
        res.json({
            message: "Berhasil mengambil data produk",
            data: product
        });
    } catch (error) {
        res.status(500).json({
            message: "Gagal mengambil data produk",
            error: error.message
        })
    }
}
// POST tambah product
async function create(req, res) {
    try {
        const product = await productModel.createProduct(req.body);
        res.status(201).json({
            message: "Berhasil menambahkan data produk",
            data: product
        });
    } catch (error) {
        res.status(500).json({
            message: "Gagal menambahkan data produk",
            error: error.message
        })
    }
}
// PUT update product
async function update(req, res) {
    try {
        const product = await productModel.updateProduct(
            req.params.id,
            req.body
        );
        res.json({
            message: "Berhasil mengubah data produk",
            data: product
        });
    } catch (error) {
        res.status(500).json({
            message: "Gagal mengubah data produk",
            error: error.message
        })
    }
}
// DELETE hapus product
async function remove(req, res) {
    try {
        const deleted = await productModel.deleteProduct(req.params.id);
        res.json({
            message: "Berhasil menghapus data produk",
            data: deleted
        });
    } catch (error) {
        res.status(500).json({
            message: "Gagal menghapus data produk",
            error: error.message
        })
    }
}
module.exports = {
    index,
    getById,
    create,
    update,
    remove
};