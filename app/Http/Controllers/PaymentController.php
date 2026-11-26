<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

/** Charges, payments and outstanding balances of students and trainees. */
class PaymentController extends Controller
{
    /** Which account type (student / trainee) the logged-in user may handle. */
    private function allowedType(?string $type): string
    {
        $role = auth()->user()->role;

        if ($role === 'shs_staff') {
            return 'student';
        }
        if ($role === 'driving_staff') {
            return 'trainee';
        }

        return in_array($type, ['student', 'trainee'], true) ? $type : 'student';
    }

    private function owner(string $type, int $id)
    {
        abort_unless(in_array($type, ['student', 'trainee'], true), 404);
        abort_unless($this->allowedType($type) === $type, 403);

        $class = Relation::getMorphedModel($type);

        return $class::findOrFail($id);
    }

    public function index(Request $request)
    {
        $type   = $this->allowedType($request->query('type'));
        $class  = Relation::getMorphedModel($type);
        $table  = app($class)->getTable();
        $term   = trim((string) $request->query('q', ''));
        $filter = $request->query('filter');

        $query = $class::query()
            ->withSum('charges as total_charges', 'amount')
            ->withSum('payments as total_paid', 'amount');

        if ($term !== '') {
            $query->where(fn ($w) => $w->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%"));
        }

        $due = "(select coalesce(sum(amount),0) from charges where charges.payable_type = ? and charges.payable_id = {$table}.id)"
             ." - (select coalesce(sum(amount),0) from payments where payments.payable_type = ? and payments.payable_id = {$table}.id)";

        if ($filter === 'balance') {
            $query->whereRaw("({$due}) > 0", [$type, $type]);
        } elseif ($filter === 'settled') {
            $query->whereRaw("({$due}) <= 0", [$type, $type]);
        }

        return view('payments.index', [
            'type'     => $type,
            'term'     => $term,
            'filter'   => $filter,
            'accounts' => $query->orderBy('last_name')->paginate(15)->withQueryString(),
            'charged'  => (float) Charge::where('payable_type', $type)->sum('amount'),
            'collected' => (float) Payment::where('payable_type', $type)->sum('amount'),
        ]);
    }

    public function account(string $type, int $id)
    {
        $owner = $this->owner($type, $id);

        return view('payments.account', [
            'type'     => $type,
            'owner'    => $owner,
            'charges'  => $owner->charges()->latest()->get(),
            'payments' => $owner->payments()->with('receiver')->latest('paid_at')->latest('id')->get(),
            'nextOr'   => 'OR-'.date('Y').'-'.str_pad((string) (Payment::max('id') + 1), 5, '0', STR_PAD_LEFT),
        ]);
    }

    public function storeCharge(Request $request, string $type, int $id)
    {
        $owner = $this->owner($type, $id);

        $data = $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01|max:9999999',
            'due_date'    => 'nullable|date',
        ]);

        $owner->charges()->create($data);

        return back()->with('success', 'Charge added.');
    }

    public function storePayment(Request $request, string $type, int $id)
    {
        $owner   = $this->owner($type, $id);
        $balance = round($owner->balance, 2);

        if ($balance <= 0) {
            return back()->with('error', 'This account is already settled.');
        }

        $data = $request->validate([
            'or_number' => 'required|string|max:30|unique:payments,or_number',
            'amount'    => ['required', 'numeric', 'min:0.01', "max:{$balance}"],
            'method'    => 'required|in:cash,gcash,bank,other',
            'paid_at'   => 'required|date',
            'remarks'   => 'nullable|string|max:255',
        ], ['amount.max' => 'The amount cannot be more than the remaining balance (₱'.number_format($balance, 2).').']);

        $payment = $owner->payments()->create($data + ['received_by' => auth()->id()]);

        return redirect()->route('payments.account', [$type, $id])
            ->with('success', 'Payment recorded. Receipt '.$payment->or_number.' is ready to print.')
            ->with('receipt', $payment->id);
    }

    public function receipt(Payment $payment)
    {
        $owner = $this->owner($payment->payable_type, $payment->payable_id);

        return view('payments.receipt', ['payment' => $payment->load('receiver'), 'owner' => $owner]);
    }
}
