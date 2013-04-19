#include <iostream>
#include <string>
#include <map>
#include <vector>
#include <set>

using namespace std;

long long solve(string S, int L) {
	map<pair<int, string>, long long> possibles;
	possibles[make_pair(0, "")] = 1;
	long long allstuff = 0;
	for (int i = 1; i <= L; ++i) {
		map<pair<int, string>, long long> new_possibles;
		for (map<pair<int, string>, long long>::const_iterator it = possibles.begin(); it != possibles.end(); ++it) {
			string code = it->first.second;
			while (code.size() < 3) code += "0";
			if (code == S.substr(1)) {
				allstuff = (allstuff + it->second) % 1000000007;
			}
		}
		if (i == L) break;
		for (map<pair<int, string>, long long>::const_iterator it = possibles.begin(); it != possibles.end(); ++it) {
			{
			string code = it->first.second;
			if (S.substr(1, code.size()) != code) continue;
			}
			for (char c = 'a'; c <= 'z'; c++) {
				pair<int, string> prev = it->first;
				int last_digit = 0;
				if (c == 'b' || c == 'f' || c == 'p' || c == 'v') last_digit = 1;
				else if (c == 'c' || c == 'g' || c == 'j' || c == 'k' || c == 'q' || c == 's' || c == 'x' || c == 'z') last_digit = 2;
				else if (c == 'd' || c == 't') last_digit = 3;
				else if (c == 'l') last_digit = 4;
				else if (c == 'm' || c == 'n') last_digit = 5;
				else if (c == 'r') last_digit = 6;
				else if (c == 'h' || c == 'w') last_digit = prev.first;
				else last_digit = 0;
				string code = prev.second;
				if (last_digit == prev.first || last_digit == 0) {
				} else {
					code += ('0' + last_digit);
				}
				if (code.size() > 3) {
					code = code.substr(0, 3);
				}
				long long ways = new_possibles[make_pair(last_digit, code)] + it->second;
				new_possibles[make_pair(last_digit, code)] = ways % 1000000007;
			}
		}
		possibles = new_possibles;
	}
	return allstuff;
}

int main() {
	int T;
	cin >> T;
	for (int t = 0; t < T; ++t) {
		string S;
		int L;
		cin >> S >> L;
		cout << solve(S, L) << endl;
	}
}
