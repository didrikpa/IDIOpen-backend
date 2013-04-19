import java.math.BigInteger;
import java.util.Scanner;

/**
 * Created with IntelliJ IDEA.
 * User: Tor
 * Date: 14.04.13
 * Time: 11:50
 * To change this template use File | Settings | File Templates.
 */
public class H {

    public static void main(String[] args) {
        new H().go();
    }

    private void go() {
        Scanner s = new Scanner(System.in);
        int numCases = Integer.parseInt(s.nextLine());
        while(numCases-->0) {
            BigInteger number = isNumber(s.nextLine());
            if(number == null)
                System.out.println("invalid input");
            else
                System.out.println(number);
        }
    }

    public BigInteger isNumber(String number) {
        try {
            BigInteger bigInt = new BigInteger(number.trim());
            if(bigInt.compareTo(BigInteger.ZERO) < 0)
                return null;
            if(number.contains("+"))
                return null;
            return bigInt;
        } catch(Exception e) {
            return null;
        }
    }
}
