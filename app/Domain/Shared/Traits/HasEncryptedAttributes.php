<?php

namespace App\Domain\Shared\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Trait HasEncryptedAttributes
 * 
 * Menyediakan enkripsi dan dekripsi otomatis untuk kolom sensitif.
 * Kolom yang ditandai 🔐 di database_schema.md harus tercantum dalam $encryptedAttributes.
 * 
 * Penggunaan:
 * 1. Use trait ini di model
 * 2. Definisikan property $encryptedAttributes dengan array nama kolom yang dienkripsi
 */
trait HasEncryptedAttributes
{
    /**
     * Temporary storage for original values before encryption
     */
    protected array $originalValuesBeforeEncryption = [];

    /**
     * Boot the trait
     */
    public static function bootHasEncryptedAttributes(): void
    {
        // Simpan nilai asli dan enkripsi saat menyimpan
        static::saving(function ($model) {
            $model->storeOriginalValues();
            $model->generateHashesFromOriginalValues();
            $model->encryptAttributes();
        });
    }

    /**
     * Simpan nilai asli sebelum enkripsi untuk keperluan hash
     */
    protected function storeOriginalValues(): void
    {
        foreach ($this->getEncryptedAttributes() as $attribute) {
            if (isset($this->attributes[$attribute]) && !empty($this->attributes[$attribute])) {
                // Cek apakah belum terenkripsi
                if (!$this->isEncrypted($this->attributes[$attribute])) {
                    $this->originalValuesBeforeEncryption[$attribute] = $this->attributes[$attribute];
                }
            }
        }
    }

    /**
     * Generate hash dari nilai asli (sebelum enkripsi)
     */
    protected function generateHashesFromOriginalValues(): void
    {
        // Generate NIK hash jika ada NIK original
        if (isset($this->originalValuesBeforeEncryption['nik'])) {
            $this->attributes['nik_hash'] = self::generateHash($this->originalValuesBeforeEncryption['nik']);
        }

        // Generate email hash jika email dienkripsi (jika berlaku)
        // Note: Email biasanya tidak dienkripsi agar bisa dicari
    }

    /**
     * Enkripsi atribut yang ditandai
     */
    protected function encryptAttributes(): void
    {
        foreach ($this->getEncryptedAttributes() as $attribute) {
            if (isset($this->attributes[$attribute]) && !empty($this->attributes[$attribute])) {
                // Cek apakah sudah terenkripsi (hindari double encryption)
                if (!$this->isEncrypted($this->attributes[$attribute])) {
                    $this->attributes[$attribute] = Crypt::encryptString($this->attributes[$attribute]);
                }
            }
        }
    }

    /**
     * Dekripsi atribut saat diakses
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->getEncryptedAttributes()) && !empty($value)) {
            return $this->decryptValue($value);
        }

        return $value;
    }

    /**
     * Dekripsi nilai dengan error handling
     */
    protected function decryptValue($value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Nilai mungkin belum terenkripsi atau rusak
            return $value;
        }
    }

    /**
     * Cek apakah nilai sudah terenkripsi
     * Laravel encryption dimulai dengan 'eyJpd' (base64 encoded JSON)
     */
    protected function isEncrypted(string $value): bool
    {
        // Cek apakah value adalah valid Laravel encrypted string
        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException $e) {
            return false;
        }
    }

    /**
     * Override attributesToArray agar Filament form fill juga mendekripsi
     * (attributesToArray() membaca $this->attributes langsung, tidak lewat getAttribute())
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getEncryptedAttributes() as $attribute) {
            if (isset($attributes[$attribute]) && !empty($attributes[$attribute])) {
                $attributes[$attribute] = $this->decryptValue($attributes[$attribute]);
            }
        }

        return $attributes;
    }

    /**
     * Ambil daftar kolom yang dienkripsi
     */
    protected function getEncryptedAttributes(): array
    {
        return property_exists($this, 'encryptedAttributes')
            ? $this->encryptedAttributes
            : [];
    }

    /**
     * Ambil nilai mentah (terenkripsi) untuk keperluan debugging
     */
    public function getRawAttribute(string $key): ?string
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set atribut dengan enkripsi manual
     */
    public function setEncryptedAttribute(string $key, ?string $value): void
    {
        if (!empty($value)) {
            $this->attributes[$key] = Crypt::encryptString($value);
        } else {
            $this->attributes[$key] = null;
        }
    }

    /**
     * Generate hash SHA-256 untuk pencarian
     * Digunakan untuk kolom seperti nik_hash, email_hash
     */
    public static function generateHash(string $value): string
    {
        return hash('sha256', strtolower(trim($value)));
    }

    /**
     * Scope untuk pencarian berdasarkan hash
     */
    public function scopeWhereHash($query, string $column, string $value)
    {
        $hashColumn = $column . '_hash';
        return $query->where($hashColumn, self::generateHash($value));
    }
}
