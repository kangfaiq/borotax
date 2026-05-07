<?php

namespace App\Http\Controllers\Web;

use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Domain\Shared\Models\DataChangeRequest;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class PortalVerificationController extends Controller
{
    public function dataChangeRequestIndex()
    {
        $requests = $this->buildDataChangeRequestQuery()
            ->paginate(10)
            ->withQueryString();

        return view('portal.data-change-requests.index', compact('requests'));
    }

    public function dataChangeRequestShow(string $requestId)
    {
        $requestRecord = $this->buildDataChangeRequestQuery()->findOrFail($requestId);
        $entity = $requestRecord->getEntityModel();

        return view('portal.data-change-requests.detail', compact('requestRecord', 'entity'));
    }

    public function stpdManualIndex()
    {
        $stpds = $this->buildStpdManualQuery()
            ->paginate(10)
            ->withQueryString();

        return view('portal.stpd-manual.index', compact('stpds'));
    }

    public function stpdManualShow(string $stpdId)
    {
        $stpd = $this->buildStpdManualQuery()->findOrFail($stpdId);

        return view('portal.stpd-manual.detail', compact('stpd'));
    }

    public function gebyarIndex()
    {
        $submissions = $this->buildGebyarQuery()
            ->paginate(10)
            ->withQueryString();

        return view('portal.gebyar.index', compact('submissions'));
    }

    public function gebyarShow(string $submissionId)
    {
        $submission = $this->buildGebyarQuery()->findOrFail($submissionId);

        return view('portal.gebyar.detail', compact('submission'));
    }

    private function buildDataChangeRequestQuery(): Builder
    {
        $owner = $this->resolveOwnerScope();

        return DataChangeRequest::query()
            ->with(['requester', 'reviewer', 'verificationStatusHistories.actor'])
            ->where(function (Builder $query) use ($owner): void {
                $hasClause = false;

                if ($owner['wajibPajakId']) {
                    $query->where(function (Builder $nestedQuery) use ($owner): void {
                        $nestedQuery->where('entity_type', 'wajib_pajak')
                            ->where('entity_id', $owner['wajibPajakId']);
                    });

                    $hasClause = true;
                }

                if ($owner['taxObjectIds']->isNotEmpty()) {
                    $method = $hasClause ? 'orWhere' : 'where';

                    $query->{$method}(function (Builder $nestedQuery) use ($owner): void {
                        $nestedQuery->where('entity_type', 'tax_objects')
                            ->whereIn('entity_id', $owner['taxObjectIds']->all());
                    });

                    $hasClause = true;
                }

                if (! $hasClause) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderByDesc('created_at');
    }

    private function buildStpdManualQuery(): Builder
    {
        return StpdManual::query()
            ->with([
                'tax.jenisPajak',
                'tax.taxObject',
                'verificationStatusHistories.actor',
            ])
            ->whereHas('tax', function (Builder $query): void {
                $query->where('user_id', auth()->id());
            })
            ->orderByDesc('tanggal_buat')
            ->orderByDesc('created_at');
    }

    private function buildGebyarQuery(): Builder
    {
        return GebyarSubmission::query()
            ->with(['jenisPajak', 'verificationStatusHistories.actor'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at');
    }

    /**
     * @return array{wajibPajakId: ?string, taxObjectIds: Collection<int, string>}
     */
    private function resolveOwnerScope(): array
    {
        $wajibPajak = WajibPajak::query()
            ->with('taxObjects:id,npwpd')
            ->where('user_id', auth()->id())
            ->first();

        return [
            'wajibPajakId' => $wajibPajak?->id,
            'taxObjectIds' => $wajibPajak?->taxObjects->pluck('id') ?? collect(),
        ];
    }
}
