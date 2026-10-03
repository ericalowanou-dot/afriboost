@csrf
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="client_name" class="ab-label">Client / marque</label>
        <input id="client_name" name="client_name" value="{{ old('client_name', $campaign->client_name) }}" required class="ab-input">
    </div>
    <div>
        <label for="title" class="ab-label">Nom de la campagne</label>
        <input id="title" name="title" value="{{ old('title', $campaign->title) }}" required class="ab-input">
    </div>
    <div class="sm:col-span-2">
        <label for="objective" class="ab-label">Objectif de communication</label>
        <textarea id="objective" name="objective" rows="3" class="ab-input">{{ old('objective', $campaign->objective) }}</textarea>
    </div>
    <div>
        <label for="budget_usd" class="ab-label">Budget (USD)</label>
        <input id="budget_usd" name="budget_usd" type="number" step="0.01" min="0" value="{{ old('budget_usd', $campaign->budget_usd) }}" class="ab-input">
    </div>
    <div>
        <label for="status" class="ab-label">Statut</label>
        <select id="status" name="status" class="ab-input">
            @foreach (\App\Models\Campaign::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $campaign->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="starts_at" class="ab-label">Début</label>
        <input id="starts_at" name="starts_at" type="date" value="{{ old('starts_at', optional($campaign->starts_at)->toDateString()) }}" class="ab-input">
    </div>
    <div>
        <label for="ends_at" class="ab-label">Fin</label>
        <input id="ends_at" name="ends_at" type="date" value="{{ old('ends_at', optional($campaign->ends_at)->toDateString()) }}" class="ab-input">
    </div>
</div>
