<table>
    <thead>
    <tr>
        <th>Name</th>
        <th>IPPIS</th>
        <th>EXPECTED SAVINGS</th>
        <th>REMITTED SAVINGS</th>
        <th>EXPECTED LTL</th>
        <th>REMITTED LTL</th>
        <th>EXPECTED STL</th>
        <th>REMITTED STL</th>
        <th>EXPECTED COML</th>
        <th>REMITTED SAVINGS</th>
        <th>MEESAGE</th>
    </tr>
    </thead>
    <tbody>
    @foreach($deductions->reconciled as $deduction)
        <tr>
            <td>{{ $deduction['name'] }}</td>
            <td>{{ $deduction['ippis'] }}</td>
            <td>{{ $deduction['expected_savings'] }}</td>
            <td>{{ $deduction['remitted_savings'] }}</td>
            <td>{{ $deduction['expected_ltl'] }}</td>
            <td>{{ $deduction['remitted_ltl'] }}</td>
            <td>{{ $deduction['expected_stl'] }}</td>
            <td>{{ $deduction['remitted_stl'] }}</td>
            <td>{{ $deduction['expected_coml'] }}</td>
            <td>{{ $deduction['remitted_coml'] }}</td>
            <td>{{ $deduction['message'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>