// <select> with a leading empty placeholder option, in the creation form style.
export default function SelectField({
    placeholder,
    options,
    className = 'background-select',
    ...selectProps
}) {
    return (
        <div className={className}>
            <select {...selectProps}>
                <option value="">{placeholder}</option>
                {options.map((value) => (
                    <option key={value} value={value}>
                        {value}
                    </option>
                ))}
            </select>
        </div>
    );
}
