const productModel = require('../models/productModel');

// Batas ukuran image maksimal 2 MB
const MAX_IMAGE_SIZE = 2 * 1024 * 1024;

// Validasi Base64 image
function validateBase64Image(image) {

    // Image wajib diisi
    if (!image || typeof image !== "string" || image.trim() === "") {
        return {
            valid: false,
            message: "Field image wajib diisi"
        };
    }

    // Cek format Base64
    const base64Regex = /^[A-Za-z0-9+/]+={0,2}$/;

    if (!base64Regex.test(image) || image.length % 4 !== 0) {
        return {
            valid: false,
            message: "Field image harus berupa Base64 yang valid"
        };
    }

    // Cek ukuran image setelah di-decode
    const imageSize = Buffer.byteLength(image, "base64");

    if (imageSize > MAX_IMAGE_SIZE) {
        return {
            valid: false,
            message: "Ukuran image maksimal 2 MB"
        };
    }

    return {
        valid: true
    };
}

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
        const imageValidation = validateBase64Image(req.body.image);

if (!imageValidation.valid) {
    return res.status(400).json({
        message: imageValidation.message
    });
}
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

        // Validasi image
        const imageValidation = validateBase64Image(req.body.image);

        if (!imageValidation.valid) {
            return res.status(400).json({
                message: imageValidation.message
            });
        }
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