<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use Illuminate\Http\Request;

class MasterDataController extends BaseController
{
    /**
     * Get Daftar Provinsi
     */
    public function getProvinces()
    {
        $provinces = Province::orderBy('name')->get(['code', 'name']);
        return $this->sendResponse($provinces, 'Daftar Provinsi.');
    }

    /**
     * Get Daftar Kabupaten by Provinsi
     */
    public function getRegencies(Request $request)
    {
        $provinceCode = $request->query('province_code');

        $query = Regency::orderBy('name');
        if ($provinceCode) {
            $query->where('province_code', $provinceCode);
        }

        $regencies = $query->get(['code', 'name', 'province_code']);
        return $this->sendResponse($regencies, 'Daftar Kabupaten/Kota.');
    }

    /**
     * Get Daftar Kecamatan by Kabupaten
     */
    public function getDistricts(Request $request)
    {
        $regencyCode = $request->query('regency_code');

        $query = District::orderBy('name');
        if ($regencyCode) {
            $query->where('regency_code', $regencyCode);
        }

        $districts = $query->get(['code', 'name', 'regency_code']);
        return $this->sendResponse($districts, 'Daftar Kecamatan.');
    }

    /**
     * Get Daftar Kelurahan by Kecamatan
     */
    public function getVillages($districtCode)
    {
        $villages = Village::where('district_code', $districtCode)
            ->orderBy('name')
            ->get(['code', 'name', 'postal_code']);

        if ($villages->isEmpty()) {
            return $this->sendError('Kelurahan tidak ditemukan untuk kode kecamatan tersebut.', [], 404);
        }

        return $this->sendResponse($villages, 'Daftar Kelurahan.');
    }

    /**
     * Get Jenis Pajak
     */
    public function getTaxTypes()
    {
        $taxTypes = JenisPajak::where('is_active', true)
            ->orderBy('urutan')
            ->get(['id', 'kode', 'nama', 'nama_singkat', 'icon', 'tipe_assessment']);

        return $this->sendResponse($taxTypes, 'Daftar Jenis Pajak.');
    }
}
