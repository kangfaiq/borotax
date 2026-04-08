<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Policies\WajibPajakPolicy;
use App\Domain\Tax\Models\Tax;
use App\Policies\TaxPolicy;
use App\Domain\Tax\Models\TaxObject;
use App\Policies\TaxObjectPolicy;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Policies\ReklameRequestPolicy;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Policies\SkpdReklamePolicy;
use App\Domain\AirTanah\Models\MeterReport;
use App\Policies\MeterReportPolicy;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Policies\SkpdAirTanahPolicy;
use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Policies\GebyarSubmissionPolicy;
use App\Domain\Shared\Models\ActivityLog;
use App\Policies\ActivityLogPolicy;
use App\Domain\Master\Models\JenisPajak;
use App\Policies\JenisPajakPolicy;
use App\Domain\Master\Models\SubJenisPajak;
use App\Policies\SubJenisPajakPolicy;
use App\Domain\Master\Models\Pimpinan;
use App\Policies\PimpinanPolicy;
use App\Domain\Auth\Models\User;
use App\Policies\UserPolicy;
use App\Domain\CMS\Models\Destination;
use App\Policies\DestinationPolicy;
use App\Domain\CMS\Models\News;
use App\Policies\NewsPolicy;
use App\Domain\Shared\Models\AppVersion;
use App\Policies\AppVersionPolicy;
use App\Domain\Region\Models\District;
use App\Policies\DistrictPolicy;
use App\Domain\Region\Models\Village;
use App\Policies\VillagePolicy;
use App\Domain\Shared\Models\DataChangeRequest;
use App\Policies\DataChangeRequestPolicy;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Policies\HargaPatokanMblbPolicy;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Policies\HargaPatokanSarangWaletPolicy;
use App\Domain\Tax\Models\HargaSatuanListrik;
use App\Policies\HargaSatuanListrikPolicy;
use App\Domain\Tax\Models\PembetulanRequest;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Policies\PembetulanRequestPolicy;
use App\Policies\PortalMblbSubmissionPolicy;
use App\Domain\AirTanah\Models\NpaAirTanah;
use App\Policies\NpaAirTanahPolicy;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Policies\AsetReklamePemkabPolicy;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Policies\HargaPatokanReklamePolicy;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Policies\KelompokLokasiJalanPolicy;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Policies\PermohonanSewaReklamePolicy;
use App\Domain\Retribusi\Models\SkrdSewaRetribusi;
use App\Policies\SkrdSewaRetribusiPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        WajibPajak::class => WajibPajakPolicy::class,
        Tax::class => TaxPolicy::class,
        TaxObject::class => TaxObjectPolicy::class,
        ReklameRequest::class => ReklameRequestPolicy::class,
        SkpdReklame::class => SkpdReklamePolicy::class,
        MeterReport::class => MeterReportPolicy::class,
        SkpdAirTanah::class => SkpdAirTanahPolicy::class,
        GebyarSubmission::class => GebyarSubmissionPolicy::class,
        ActivityLog::class => ActivityLogPolicy::class,
        JenisPajak::class => JenisPajakPolicy::class,
        SubJenisPajak::class => SubJenisPajakPolicy::class,
        Pimpinan::class => PimpinanPolicy::class,
        User::class => UserPolicy::class,
        Destination::class => DestinationPolicy::class,
        News::class => NewsPolicy::class,
        AppVersion::class => AppVersionPolicy::class,
        District::class => DistrictPolicy::class,
        Village::class => VillagePolicy::class,
        DataChangeRequest::class => DataChangeRequestPolicy::class,
        PembetulanRequest::class => PembetulanRequestPolicy::class,
        PortalMblbSubmission::class => PortalMblbSubmissionPolicy::class,
        HargaPatokanMblb::class => HargaPatokanMblbPolicy::class,
        HargaPatokanSarangWalet::class => HargaPatokanSarangWaletPolicy::class,
        HargaSatuanListrik::class => HargaSatuanListrikPolicy::class,
        NpaAirTanah::class => NpaAirTanahPolicy::class,
        AsetReklamePemkab::class => AsetReklamePemkabPolicy::class,
        HargaPatokanReklame::class => HargaPatokanReklamePolicy::class,
        KelompokLokasiJalan::class => KelompokLokasiJalanPolicy::class,
        PermohonanSewaReklame::class => PermohonanSewaReklamePolicy::class,
        SkrdSewaRetribusi::class => SkrdSewaRetribusiPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
