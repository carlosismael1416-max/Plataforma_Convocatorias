<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <x-editor-enriquecido :campo="$getStatePath()" :editable="! $isDisabled()" :limite="$getLimite()" :placeholder="$getMarcador()" />
</x-dynamic-component>
