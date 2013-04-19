#include <iostream>
#include <string>
#include <map>
#include <vector>
#include <set>
#include <algorithm>

using namespace std;

int main() {
	int T;
	cin >> T;
	string s;
	getline(cin, s);
	for (int t = 0; t < T; ++t) {
		getline(cin, s);
		while (s[0] == ' ') s = s.substr(1);
		reverse(s.begin(), s.end());
		while (s[0] == ' ') s = s.substr(1);
		reverse(s.begin(), s.end());
		bool valid = true;
		for (char c: s) {
			if (c >= '0' && c <= '9') {
			} else {
				valid = false;
			}
		}
		while (s[0] == '0') s = s.substr(1);
		if (s == "") s = "0";
		if (valid) {
			cout << s << endl;
		} else {
			cout << "invalid input" << endl;
		}
	}
}
